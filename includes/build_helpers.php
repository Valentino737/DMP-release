<?php
/**
 * Sdílené pomocné funkce pro správu sestav používané konfigurátorem, build_finish a API aktualizace sestavy.
 */

/**
 * Kontrola kompatibility vybraných komponent.
 * Vrací ['errors' => [...], 'warnings' => [...], 'cpuError' => bool, ...]
 */
function checkCompatibility($build) {
    $errors = [];
    $warnings = [];

    $cpuError     = false;
    $ramError     = false;
    $gpuError     = false;
    $caseError    = false;
    $storageError = false;
    $coolingError = false;
    $psuError     = false;
    $moboError    = false;

    $cpu     = $build['cpu'] ?? null;
    $mb      = $build['motherboard'] ?? null;
    $ram     = $build['ram'] ?? null;
    $gpu     = $build['gpu'] ?? null;
    $psu     = $build['psu'] ?? null;
    $case    = $build['case'] ?? null;
    $storage = $build['storage'] ?? [];
    $cooler  = $build['cooling'] ?? null;

    // Pomocné funkce pro bezpečný přístup
    $safeGet = function($arr, $key) {
        if (!is_array($arr)) return null;
        return $arr[$key] ?? null;
    };
    $safeInt = function($arr, $key) use ($safeGet) {
        $val = $safeGet($arr, $key);
        if ($val === null) return null;
        if (is_numeric($val)) return (int)$val;
        if (is_string($val) && preg_match('/(\d+)/', $val, $m)) return (int)$m[1];
        return null;
    };
    $safeStr = function($arr, $key) use ($safeGet) {
        $v = $safeGet($arr, $key);
        return is_string($v) ? $v : ($v !== null ? (string)$v : null);
    };

    /* =========================
       CPU ↔ Základní deska (socket)
       ========================= */
    if (is_array($cpu) && is_array($mb)) {
        $cpuSocket = $safeStr($cpu, 'socket');
        $mbSocket  = $safeStr($mb, 'socket');

        if ($cpuSocket && $mbSocket && $cpuSocket !== $mbSocket) {
            $errors[] = 'Procesor a základní deska mají nekompatibilní sockety';
            $cpuError = true;
            $moboError = true;
        }
    }

    /* =========================
       RAM ↔ Základní deska
       ========================= */
    if (is_array($ram) && is_array($mb)) {
        $mbRamType  = $safeStr($mb, 'ram_type');
        $mbMaxMem   = $safeInt($mb, 'max_ram');
        $mbSlots    = $safeInt($mb, 'ram_slots');
        $mbSpeed    = $safeInt($mb, 'ram_speed');

        $ramType     = $safeStr($ram, 'type') ?? $safeStr($ram, 'ram') ?? null;
        $ramCapacity = $safeInt($ram, 'capacity');
        $ramModules  = $safeInt($ram, 'modules');
        $ramSpeed    = $safeInt($ram, 'speed');

        if ($mbRamType && $ramType && $ramType !== $mbRamType) {
            $errors[] = 'Paměť neodpovídá typu podporovanému základní deskou';
            $ramError = true;
            $moboError = true;
        }

        if ($mbMaxMem && $ramCapacity !== null && $ramCapacity > $mbMaxMem) {
            $errors[] = 'Kapacita RAM překračuje maximum základní desky';
            $ramError = true;
        }

        if ($mbSlots && $ramModules !== null && $ramModules > $mbSlots) {
            $errors[] = 'Základní deska nemá dostatek RAM slotů';
            $ramError = true;
            $moboError = true;
        }

        if ($mbSpeed && $ramSpeed !== null && $ramSpeed > $mbSpeed) {
            $warnings[] = 'RAM poběží na nižší frekvenci, než je její maximum';
        }
    }

    /* =========================
       RAM ↔ CPU
       ========================= */
    if (is_array($cpu) && is_array($ram)) {
        $cpuRam = $safeStr($cpu, 'ram');
        $ramType = $safeStr($ram, 'type') ?? $safeStr($ram, 'ram') ?? null;

        if ($cpuRam && $ramType && $cpuRam !== $ramType) {
            $errors[] = 'Procesor nepodporuje zvolený typ operační paměti';
            $cpuError = true;
            $ramError = true;
        }
    }

    /* =========================
       Základní deska ↔ Skříň (formát)
       Používá sloupec `mboard_type` typu SET, který přímo obsahuje podporované formáty základních desek.
       ========================= */
    if (is_array($mb) && is_array($case)) {
        $mbForm       = $safeStr($mb, 'form_factor');
        $caseMbTypes  = $safeStr($case, 'mboard_type');

        if ($mbForm && $caseMbTypes) {
            // mboard_type je SET např. 'Mini-ITX,Micro-ATX,ATX'
            $supportedList = array_map('trim', explode(',', $caseMbTypes));
            if (!in_array($mbForm, $supportedList, true)) {
                $errors[] = 'Skříň nepodporuje formát základní desky (' . $mbForm . ')';
                $caseError = true;
                $moboError = true;
            }
        }
    }

    /* =========================
       GPU ↔ Skříň (délka)
       ========================= */
    if (is_array($gpu) && is_array($case)) {
        $gpuLen = $safeInt($gpu, 'length') ?? 0;
        $caseMaxGpu = $safeInt($case, 'max_gpu') ?? 0;

        if ($gpuLen > 0 && $caseMaxGpu > 0 && $gpuLen > $caseMaxGpu) {
            $errors[] = 'Grafická karta je příliš dlouhá pro zvolenou skříň: GPU je ' . $gpuLen . 'mm, ale skříň pojme maximálně ' . $caseMaxGpu . 'mm';
            $gpuError = true;
            $caseError = true;
        }
    }

    /* =========================
       CPU iGPU ↔ Přítomnost GPU
       ========================= */
    if (is_array($cpu) && !$gpu) {
        $cpuGraphics = trim($safeStr($cpu, 'graphics') ?? '');
        if ($cpuGraphics === '' || strtolower($cpuGraphics) === 'none') {
            $warnings[] = 'Procesor nemá integrovanou grafiku a není vybrána žádná GPU';
        }
    }

    /* =========================
       CPU TDP ↔ Chladič
       ========================= */
    if ($cpu && $cooler) {
        $cpuTdp = is_numeric($cpu['tdp'] ?? null) ? (int)$cpu['tdp'] : null;
        $coolerMax = is_numeric($cooler['tdp'] ?? null) ? (int)$cooler['tdp'] : null;
        if ($cpuTdp && $coolerMax && $cpuTdp > $coolerMax) {
            $errors[] = 'Chladič nemusí zvládnout TDP procesoru: CPU má ' . $cpuTdp . 'W, ale chladič zvládne max. ' . $coolerMax . 'W';
            $coolingError = true;
        }
    }

    /* =========================
       Chladič ↔ CPU socket
       ========================= */
    if (is_array($cpu) && is_array($cooler)) {
        $cpuSocket     = $safeStr($cpu, 'socket');
        $coolerSockets = $safeStr($cooler, 'socket_support');

        if ($cpuSocket && $coolerSockets) {
            $supportedSockets = array_map('trim', explode(',', $coolerSockets));
            if (!in_array($cpuSocket, $supportedSockets, true)) {
                $errors[] = 'Chladič nepodporuje socket procesoru (' . $cpuSocket . ')';
                $coolingError = true;
            }
        }
    }

    /* =========================
       Výška vzduchochodného chladiče ↔ Max výška skříně
       ========================= */
    if (is_array($cooler) && is_array($case)) {
        $coolerType   = $safeStr($cooler, 'type');
        $coolerHeight = $safeInt($cooler, 'height');
        $caseMaxCool  = $safeInt($case, 'max_cooler');

        if ($coolerType === 'Air' && $coolerHeight && $caseMaxCool && $coolerHeight > $caseMaxCool) {
            $errors[] = 'Chladič je příliš vysoký pro skříň: chladič má ' . $coolerHeight . 'mm, ale skříň pojme max. ' . $caseMaxCool . 'mm';
            $coolingError = true;
            $caseError = true;
        }
    }

    /* =========================
       AIO radiátor ↔ Uchycení radiátoru ve skříni
       ========================= */
    if (is_array($cooler) && is_array($case)) {
        $coolerType = $safeStr($cooler, 'type');
        $radSize    = $safeInt($cooler, 'radiator_size');

        if ($coolerType === 'AIO' && $radSize) {
            $frontRad = $safeInt($case, 'front_rad') ?? 0;
            $topRad   = $safeInt($case, 'top_rad') ?? 0;
            $rearRad  = $safeInt($case, 'rear_rad') ?? 0;
            $maxRad   = max($frontRad, $topRad, $rearRad);

            if ($maxRad > 0 && $radSize > $maxRad) {
                $errors[] = 'Radiátor AIO chlazení je příliš velký pro skříň: radiátor je ' . $radSize . 'mm, ale skříň pojme max. ' . $maxRad . 'mm';
                $coolingError = true;
                $caseError = true;
            }
        }
    }

    /* =========================
       Úložiště ↔ Základní deska
       ========================= */
    if (!empty($storage) && $mb && is_array($storage)) {
        $sataUsed = 0;
        $m2Used   = 0;

        foreach ($storage as $drive) {
            if (is_array($drive)) {
                $interface = strtolower($drive['interface'] ?? '');
                if (str_contains($interface, 'sata')) {
                    $sataUsed++;
                }
                if (str_contains($interface, 'pcie') || str_contains($interface, 'm.2')) {
                    $m2Used++;
                }
            }
        }

        $mbSata = $safeInt($mb, 'sata_slots') ?? 0;
        $mbM2   = $safeInt($mb, 'm2_slots') ?? 0;

        if ($sataUsed > $mbSata) {
            if ($mbSata < 1) {
                $errors[] = 'Základní deska nemá žádné SATA porty, nelze připojit SATA disky';
            } else {
                $errors[] = 'Příliš mnoho SATA disků: máte ' . $sataUsed . ', ale deska podporuje maximálně ' . $mbSata . ' port(ů)';
            }
            $storageError = true;
        }

        if ($m2Used > $mbM2) {
            if ($mbM2 < 1) {
                $errors[] = 'Základní deska nemá žádné M.2 sloty, nelze připojit M.2 disky';
            } else {
                $errors[] = 'Příliš mnoho M.2 disků: máte ' . $m2Used . ', ale deska podporuje maximálně ' . $mbM2 . ' slot(ů)';
            }
            $storageError = true;
        }
    }

    /* =========================
       Zdroj ↔ Kontrola výkonu
       ========================= */
    if ($psu) {
        $psuW = is_numeric($psu['power'] ?? null) ? (int)$psu['power'] : 0;
        $cpuTdp = is_numeric($cpu['tdp'] ?? null) ? (int)$cpu['tdp'] : 0;
        $gpuTdp = is_numeric($gpu['tdp'] ?? null) ? (int)$gpu['tdp'] : 0;
        $estimated = $cpuTdp + $gpuTdp + 150;
        if ($psuW > 0 && $estimated > $psuW) {
            $errors[] = 'Zdroj je příliš slabý: sestava vyžaduje odhadem ' . $estimated . 'W, ale vybraný zdroj zvládne maximálně ' . $psuW . 'W. Vyberte silnější zdroj nebo slabší komponenty.';
            $psuError = true;
        }

        /* Zdroj ↔ Formát skříně */
        if ($case) {
            $psuType  = $safeStr($psu, 'type');
            $casePsuType = $safeStr($case, 'psu_type');

            if ($psuType && $casePsuType && $psuType !== $casePsuType) {
                $errors[] = 'PSU není kompatibilní se skříní: vybraný zdroj je ' . $psuType . ', ale skříň vyžaduje ' . $casePsuType;
                $psuError = true;
                $caseError = true;
            }
        }
    }

    return [
        'errors'       => $errors,
        'warnings'     => $warnings,
        'cpuError'     => $cpuError,
        'ramError'     => $ramError,
        'gpuError'     => $gpuError,
        'caseError'    => $caseError,
        'storageError' => $storageError,
        'coolingError' => $coolingError,
        'psuError'     => $psuError,
        'moboError'    => $moboError,
    ];
}

/**
 * Výpočet celkové ceny sestavy.
 */
function calculateBuildPrice($build) {
    $total = 0;

    $components = ['cpu', 'gpu', 'ram', 'motherboard', 'psu', 'case', 'cooling'];
    foreach ($components as $comp) {
        if (!empty($build[$comp]) && is_array($build[$comp])) {
            $total += (float)($build[$comp]['price'] ?? 0);
        }
    }

    if (!empty($build['storage']) && is_array($build['storage'])) {
        foreach ($build['storage'] as $storage) {
            if (is_array($storage)) {
                $total += (float)($storage['price'] ?? 0);
            }
        }
    }

    return $total;
}

/**
 * Mapování typu komponenty → FK sloupec v tabulce parts.
 */
function getComponentMap() {
    return [
        'cpu'         => 'partId_cpu',
        'gpu'         => 'partId_gpu',
        'ram'         => 'partId_ram',
        'motherboard' => 'partId_mboard',
        'storage'     => 'partId_storage',
        'psu'         => 'partId_psu',
        'case'        => 'partId_case',
        'cooling'     => 'partId_cooler',
    ];
}

/**
 * Uložení komponent sestavy do tabulek parts + used_parts.
 * Musí být voláno uvnitř transakce.
 *
 * @param PDO   $pdo     Připojení k databázi
 * @param int   $buildId ID řádku sestavy
 * @param array $build   Pole sestavy ze session
 * @return int[] Pole vytvořených ID dílů
 */
function saveBuildComponents(PDO $pdo, int $buildId, array $build): array {
    $componentMap = getComponentMap();
    $usedPartIds = [];

    foreach ($componentMap as $componentType => $fkColumn) {
        if (empty($build[$componentType])) {
            continue;
        }

        $component = $build[$componentType];

        if (!is_array($component)) {
            continue;
        }

        if ($componentType === 'storage') {
            foreach ($component as $drive) {
                if (is_array($drive) && isset($drive['id'])) {
                    $driveId = (int)$drive['id'];

                    $partStmt = $pdo->prepare("
                        INSERT INTO parts (name, $fkColumn, brandId, typeId, price, createdAt, updatedAt)
                        VALUES (?, ?, 1, 1, ?, NOW(), NOW())
                    ");
                    $partStmt->execute([
                        $drive['name'] ?? 'Úložiště',
                        $driveId,
                        (float)($drive['price'] ?? 0)
                    ]);

                    $usedPartIds[] = (int)$pdo->lastInsertId();
                }
            }
        } else {
            $componentId = (int)($component['id'] ?? 0);
            if ($componentId > 0) {
                $partStmt = $pdo->prepare("
                    INSERT INTO parts (name, $fkColumn, brandId, typeId, price, createdAt, updatedAt)
                    VALUES (?, ?, 1, 1, ?, NOW(), NOW())
                ");
                $partStmt->execute([
                    $component['name'] ?? ucfirst($componentType),
                    $componentId,
                    (float)($component['price'] ?? 0)
                ]);

                $usedPartIds[] = (int)$pdo->lastInsertId();
            }
        }
    }

    // Propojení dílů se sestavou
    foreach ($usedPartIds as $partId) {
        $usedStmt = $pdo->prepare('INSERT INTO used_parts (buildId, partId) VALUES (?, ?)');
        $usedStmt->execute([$buildId, $partId]);
    }

    return $usedPartIds;
}

/**
 * Smazání used_parts A příslušných záznamů parts pro sestavu.
 * Musí být voláno uvnitř transakce.
 */
function deleteBuildParts(PDO $pdo, int $buildId): void {
    // Získání ID dílů před smazáním vazeb
    $stmt = $pdo->prepare('SELECT partId FROM used_parts WHERE buildId = ?');
    $stmt->execute([$buildId]);
    $partIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Smazání vazeb used_parts
    $stmt = $pdo->prepare('DELETE FROM used_parts WHERE buildId = ?');
    $stmt->execute([$buildId]);

    // Smazání osiřelých záznamů parts
    if (!empty($partIds)) {
        $placeholders = implode(',', array_fill(0, count($partIds), '?'));
        $stmt = $pdo->prepare("DELETE FROM parts WHERE id IN ($placeholders)");
        $stmt->execute($partIds);
    }
}
