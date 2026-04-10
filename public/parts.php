<?php
/**
 * Katalog komponent
 * 
 * Zobrazuje všechny dostupné komponenty (CPU, GPU, RAM atd.)
 * s možností filtrovat podle kategorie a vyhledávat.
 */
session_start();
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/csrf.php';

$currentPage = 'parts.php';
$category = $_GET['category'] ?? 'all';
$q = trim($_GET['q'] ?? '');

$tables = [
		'cpu' => 'CPU',
		'gpu' => 'GPU',
		'ram' => 'RAM',
		'motherboard' => 'Základní deska',
		'storage' => 'Úložiště',
		'psu' => 'PSU',
		'case' => 'Skříň',
		'cooler' => 'Chlazení'
];

function fetchItems($pdo, $table, $q) {
		try {
				if ($q !== '') {
						$stmt = $pdo->prepare("SELECT * FROM `" . str_replace('`','', $table) . "` WHERE name LIKE :q ORDER BY name");
						$stmt->execute([':q' => "%$q%"]);
				} else {
						$stmt = $pdo->query("SELECT * FROM `" . str_replace('`','', $table) . "` ORDER BY name");
				}
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
				return []; // tabulka nemusí existovat, vrátí prázdný seznam
		}
}

$build = $_SESSION['build'] ?? [];

// Pokud je požadována konkrétní kategorie, načte pouze tuto tabulku, jinak načte všechny
$results = [];
if ($category === 'all') {
		foreach ($tables as $tbl => $label) {
				$results[$tbl] = fetchItems($pdo, $tbl, $q);
		}
} else {
		if (array_key_exists($category, $tables)) {
				$results[$category] = fetchItems($pdo, $category, $q);
		} else {
				$results = [];
		}
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
	<meta charset="UTF-8">
	<title>Všechny komponenty</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="/dmp/assets/css/style.css">
	<style>
		body { background-color: #f9fafb; }
		.page-header { padding: 24px 0; border-bottom: 1px solid #dee2e6; margin-bottom: 24px; }
	</style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<?php include_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-4">
	<div class="page-header d-flex justify-content-between align-items-start">
		<div>
			<h1 class="h3 mb-1">Všechny komponenty</h1>
			<p class="text-muted mb-0">Procházejte a filtrujte všechny dostupné komponenty v databázi.</p>
		</div>
		<div>
			<form class="row g-2" method="GET" style="max-width:520px;">
				<div class="col-5">
					<select name="category" class="form-select">
						<option value="all" <?= $category === 'all' ? 'selected' : '' ?>>Vše</option>
						<?php foreach ($tables as $key => $label): ?>
							<option value="<?= htmlspecialchars($key) ?>" <?= $category === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-5">
					<input name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Hledat podle názvu...">
				</div>
				<div class="col-2 d-grid">
					<button class="btn btn-success">Filtrovat</button>
				</div>
			</form>
		</div>
	</div>

	<?php if (!empty($build) && is_array($build)): ?>
		<div class="mb-3">
			<strong>Aktuálně vybráno:</strong>
			<?php
				$sel = [];
				foreach (['cpu','motherboard','ram','gpu','storage','psu','case','cooling'] as $k) {
						if (!empty($build[$k])) {
								if ($k === 'storage' && is_array($build['storage'])) {
										$names = array_map(function($d){ return is_array($d) && isset($d['name']) ? $d['name'] : ''; }, $build['storage']);
										$sel[] = count($names) . 'x ' . implode(', ', array_filter($names));
								} else {
										$name = is_array($build[$k]) && isset($build[$k]['name']) ? $build[$k]['name'] : (string)$build[$k];
										$sel[] = htmlspecialchars($name);
								}
						}
				}
				echo implode(' · ', $sel);
			?>
		</div>
	<?php endif; ?>

	<!-- Tlačítko pro navržení komponentu -->
	<?php if (isset($_SESSION['user_id'])): ?>
		<div class="mb-4 d-flex justify-content-end">
			<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitComponentModal">
				📤 Navrhnout komponentu
			</button>
		</div>
	<?php else: ?>
		<div class="mb-4 alert alert-info">
			<strong>Chcete navrhnout komponentu?</strong> <a href="/dmp/public/login.php">Přihlašte se</a> nebo <a href="/dmp/public/register.php">zaregistrujte se</a> pro přidání komponent do naší databáze.
		</div>
	<?php endif; ?>

	<?php foreach ($results as $tbl => $items): ?>
		<div class="mb-4">
			<div class="d-flex justify-content-between align-items-center mb-2">
				<h4 class="h6 mb-0"><?= htmlspecialchars($tables[$tbl] ?? $tbl) ?></h4>
				<small class="text-muted"><?= count($items) ?> položek</small>
			</div>

			<?php if (empty($items)): ?>
				<div class="alert alert-light">Žádné výsledky.</div>
			<?php else: ?>
				<div class="row g-3">
					<?php foreach ($items as $item): ?>
						<div class="col-12 col-md-6 col-lg-4">
							<div class="card h-100 shadow-sm border-0" id="component-<?= $item['id'] ?? '' ?>">
								<div class="card-body d-flex flex-column">
									<h5 class="card-title mb-2"><?= htmlspecialchars($item['name'] ?? ($item['title'] ?? 'Unnamed')) ?></h5>
									<ul class="list-group list-group-flush mb-3">
										<?php foreach ($item as $field => $value): ?>
											<?php if (in_array($field, ['id','name'])) continue; ?>
											<?php if (!is_null($value) && $value !== ''): ?>
												<li class="list-group-item py-1"><strong><?= htmlspecialchars(ucwords(str_replace('_',' ',$field))) ?>:</strong> <?= htmlspecialchars($value) ?></li>
											<?php endif; ?>
										<?php endforeach; ?>
									</ul>
									<div class="mt-auto">
										<a href="/dmp/public/selections/<?= htmlspecialchars($tbl) ?>_select.php?select=<?= htmlspecialchars($item['id']) ?>" class="btn btn-success w-100">Vybrat</a>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>

</div>

<!-- Modal pro navržení komponenty -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="submitComponentModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">📤 Navrhnout novou komponentu</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<form id="submitComponentForm">
				<div class="modal-body">
					<?= csrf_field() ?>
					<div id="submitMessage"></div>

					<div class="mb-3">
						<label for="componentType" class="form-label">Typ komponenty <span class="text-danger">*</span></label>
						<select name="componentType" id="componentType" class="form-select" required>
							<option value="">Vyberte typ komponenty...</option>
							<?php foreach ($tables as $key => $label): ?>
								<option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="mb-3">
						<label for="componentName" class="form-label">Název komponenty <span class="text-danger">*</span></label>
						<input type="text" name="name" id="componentName" class="form-control" placeholder="např. Intel Core i9-14900K" required maxlength="255">
						<small class="form-text text-muted">Název komponenty tak, jak se zobrazuje v katalozích</small>
					</div>

					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="componentBrand" class="form-label">Značka</label>
							<input type="text" name="brand" id="componentBrand" class="form-control" placeholder="např. Intel, NVIDIA" maxlength="100">
						</div>
						<div class="col-md-6 mb-3">
							<label for="componentPrice" class="form-label">Cena (Kč)</label>
							<input type="number" name="price" id="componentPrice" class="form-control" step="0.01" min="0" placeholder="např. 5999.99">
						</div>
					</div>

					<div id="specificationFields"></div>

					<div class="alert alert-info" role="alert">
							<strong>ℹ️ Poznámka:</strong> Váš návrh bude přezkoumán administrátory před přidáním do databáze. Ujistěte se, že všechny informace jsou přesné a úplné.
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
					<button type="submit" class="btn btn-primary">Odeslat návrh</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
// Šablony specifikací pro každý typ komponenty
const specTemplates = {
	cpu: [
		{ name: 'core_count', label: 'Počet jader', type: 'number' },
		{ name: 'thread_count', label: 'Počet vláken', type: 'number' },
		{ name: 'base_clock', label: 'Základní frekvence (GHz)', type: 'text' },
		{ name: 'tdp', label: 'TDP (W)', type: 'number' },
		{ name: 'socket', label: 'Patice', type: 'text' }
	],
	gpu: [
		{ name: 'vram', label: 'VRAM (GB)', type: 'number' },
		{ name: 'memory_type', label: 'Typ paměti (GDDR6, atd.)', type: 'text' },
		{ name: 'tdp', label: 'TDP (W)', type: 'number' },
		{ name: 'power_connectors', label: 'Napájecí konektory', type: 'text' }
	],
	ram: [
		{ name: 'capacity', label: 'Kapacita (GB)', type: 'number' },
		{ name: 'type', label: 'Typ (DDR4, DDR5)', type: 'text' },
		{ name: 'speed', label: 'Rychlost (MHz)', type: 'number' },
		{ name: 'latency', label: 'Latence (CAS)', type: 'text' }
	],
	motherboard: [
		{ name: 'socket', label: 'Patice CPU', type: 'text' },
		{ name: 'form_factor', label: 'Formát (ATX, mATX)', type: 'text' },
		{ name: 'ram_type', label: 'Typ RAM (DDR4, DDR5)', type: 'text' },
		{ name: 'max_ram', label: 'Max. RAM (GB)', type: 'number' }
	],
	storage: [
		{ name: 'capacity', label: 'Kapacita (GB)', type: 'number' },
		{ name: 'type', label: 'Typ (Úloziště SSD, HDD)', type: 'text' },
		{ name: 'interface', label: 'Rozhraní (SATA, NVMe)', type: 'text' },
		{ name: 'speed', label: 'Rychlost (MB/s)', type: 'number' }
	],
	psu: [
		{ name: 'wattage', label: 'Výkon (W)', type: 'number' },
		{ name: 'efficiency', label: 'Certifikace účinnosti (80+, Platinum)', type: 'text' },
		{ name: 'modular', label: 'Modulární (Ano/Ne)', type: 'text' }
	],
	case: [
		{ name: 'form_factor', label: 'Formát (ATX, mATX)', type: 'text' },
		{ name: 'max_gpu_length', label: 'Max. délka GPU (mm)', type: 'number' }
	],
	cooler: [
		{ name: 'cooling_type', label: 'Typ (vzduchem, vodíkem)', type: 'text' },
		{ name: 'tdp_rating', label: 'Hodnocení TDP (W)', type: 'number' },
		{ name: 'socket_compatibility', label: 'Kompatibilita paticí', type: 'text' }
	]
};

document.getElementById('componentType').addEventListener('change', function() {
	const type = this.value;
	const specsDiv = document.getElementById('specificationFields');
	specsDiv.innerHTML = '';

	if (type && specTemplates[type]) {
		const specs = specTemplates[type];
		let html = '<div class="mb-3"><label class="form-label">Specifikace</label>';

		specs.forEach(spec => {
			html += `
				<div class="mb-2">
					<label for="spec_${spec.name}" class="form-label recipe-label">${spec.label}</label>
					<input type="${spec.type}" name="spec_${spec.name}" id="spec_${spec.name}" class="form-control" placeholder="Volitelné">
				</div>
			`;
		});

		html += '</div>';
		specsDiv.innerHTML = html;
	}
});

document.getElementById('submitComponentForm').addEventListener('submit', async function(e) {
	e.preventDefault();

	const formData = new FormData(this);
	const messageDiv = document.getElementById('submitMessage');

	try {
		const response = await fetch('/dmp/api/components/submit.php', {
			method: 'POST',
			body: formData
		});

		const data = await response.json();

		if (data.success) {
			messageDiv.innerHTML = '<div class="alert alert-success">✅ ' + data.message + '</div>';
			setTimeout(() => {
				bootstrap.Modal.getInstance(document.getElementById('submitComponentModal')).hide();
				document.getElementById('submitComponentForm').reset();
				messageDiv.innerHTML = '';
			}, 2000);
		} else {
			messageDiv.innerHTML = '<div class="alert alert-danger">❌ ' + data.message + '</div>';
		}
	} catch (error) {
			messageDiv.innerHTML = '<div class="alert alert-danger">❌ Došlo k chybě: ' + error.message + '</div>';
	}
});

// Dělá zvýraznění a posouvání k navržené komponentě, pokud je ID předáno v URL (např. po odeslání návrhu)
document.addEventListener('DOMContentLoaded', function() {
	const params = new URLSearchParams(window.location.search);
	const highlightId = params.get('highlight');
	
	if (highlightId) {
		const element = document.getElementById('component-' + highlightId);
		if (element) {
			// Přidá zvýraznění
			element.style.border = '3px solid #0066cc';
			element.style.boxShadow = '0 0 15px rgba(0, 102, 204, 0.5)';
			element.style.backgroundColor = '#f0f4ff';
			
			// Posune k elementu
			setTimeout(() => {
				element.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}, 100);
		}
	}
});
</script>
<?php endif; ?>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>