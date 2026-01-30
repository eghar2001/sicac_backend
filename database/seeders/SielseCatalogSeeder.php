<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SielseCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Path inside storage/app
        $relativePath = 'seed/catalogo_sielse_normalizado.xlsx';

        $absolutePath = storage_path('app/' . $relativePath);
        if (!is_file($absolutePath)) {
            throw new \RuntimeException("Excel not found at {$absolutePath}");
        }

        // Load excel
        $spreadsheet = IOFactory::load($absolutePath);

        // Your normalized master sheet
        $sheetName = '01_Master';
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if (!$sheet) {
            throw new \RuntimeException("Sheet '{$sheetName}' not found in excel.");
        }

        // Read all rows as array (first row = headers)
        $rows = $sheet->toArray(null, true, true, false);

        if (count($rows) < 2) {
            $this->command?->warn("No data rows found in '{$sheetName}'.");
            return;
        }

        $headers = array_map(fn($h) => trim((string) $h), $rows[0]);

        // Build header -> index map
        $idx = [];
        foreach ($headers as $i => $h) {
            if ($h !== '')
                $idx[$h] = $i;
        }

        // Expected columns (as seen in your file)
        $colExternalId = $this->requireCol($idx, 'ID');
        $colName = $this->requireCol($idx, 'Nombre');
        $colBrand = $this->requireCol($idx, 'Marca');
        $colModelSku = $this->requireCol($idx, 'Modelo/SKU');
        $colFamily = $this->requireCol($idx, 'familia');
        $colSubfamily = $this->requireCol($idx, 'subfamilia');
        $colPrice = $this->requireCol($idx, 'Precio (ARS)');
        $colCategoryBase = $this->requireCol($idx, 'Categoría_base');

        $colSpecsJson = $this->optionalCol($idx, 'Specs_JSON');
        $colTextoRag = $this->optionalCol($idx, 'Texto_RAG');
        $colRawFeatures = $this->optionalCol($idx, 'Características (raw)');

        // Caches to avoid repeated queries
        $brandIdByName = [];
        $familyIdByName = [];
        $categoryIdByName = [];
        $subfamilyIdByKey = []; // family_id|subfamily_name => id

        DB::transaction(function () use ($rows, $colExternalId, $colName, $colBrand, $colModelSku, $colFamily, $colSubfamily, $colPrice, $colCategoryBase, $colSpecsJson, $colTextoRag, $colRawFeatures, &$brandIdByName, &$familyIdByName, &$categoryIdByName, &$subfamilyIdByKey) {
            // skip header row
            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];

                $externalId = trim((string) ($row[$colExternalId] ?? ''));
                if ($externalId === '') {
                    continue;
                }

                $name = trim((string) ($row[$colName] ?? ''));
                if ($name === '') {
                    // If you want to hard-fail, throw here instead.
                    continue;
                }

                $brandName = trim((string) ($row[$colBrand] ?? ''));
                if ($brandName === '')
                    $brandName = 'SIN MARCA';

                $familyName = trim((string) ($row[$colFamily] ?? ''));
                if ($familyName === '')
                    $familyName = 'SIN FAMILIA';

                $subfamilyName = trim((string) ($row[$colSubfamily] ?? ''));
                if ($subfamilyName === '')
                    $subfamilyName = 'SIN SUBFAMILIA';

                $categoryName = trim((string) ($row[$colCategoryBase] ?? ''));
                if ($categoryName === '')
                    $categoryName = 'SIN CATEGORIA';

                $modelSku = trim((string) ($row[$colModelSku] ?? ''));
                $price = $this->parseMoney($row[$colPrice] ?? null);



                $specsJsonRaw = ($colSpecsJson !== null) ? (string) ($row[$colSpecsJson] ?? '') : '';
                $technicalSpecs = null;
                if (trim($specsJsonRaw) !== '') {
                    // Some rows might contain "{}" or invalid JSON
                    $decoded = json_decode($specsJsonRaw, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $technicalSpecs = $decoded;
                    }
                }

                // Prefer Texto_RAG; fallback to raw features; else null
                $description = null;
                if ($colTextoRag !== null) {
                    $description = trim((string) ($row[$colTextoRag] ?? ''));
                }
                if ($description === '' || $description === null) {
                    if ($colRawFeatures !== null) {
                        $description = trim((string) ($row[$colRawFeatures] ?? ''));
                    }
                }
                if ($description === '')
                    $description = null;

                // --- Upsert brand ---
                if (!isset($brandIdByName[$brandName])) {
                    $brandIdByName[$brandName] = DB::table('brands')->where('name', $brandName)->value('id')
                        ?? DB::table('brands')->insertGetId([
                            'name' => $brandName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
                $brandId = $brandIdByName[$brandName];

                // --- Upsert family ---
                if (!isset($familyIdByName[$familyName])) {
                    $familyIdByName[$familyName] = DB::table('families')->where('name', $familyName)->value('id')
                        ?? DB::table('families')->insertGetId([
                            'name' => $familyName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
                $familyId = $familyIdByName[$familyName];

                // --- Upsert subfamily (unique by family_id + name) ---
                $subKey = $familyId . '|' . $subfamilyName;
                if (!isset($subfamilyIdByKey[$subKey])) {
                    $subfamilyIdByKey[$subKey] =
                        DB::table('subfamilies')
                            ->where('family_id', $familyId)
                            ->where('name', $subfamilyName)
                            ->value('id')
                        ?? DB::table('subfamilies')->insertGetId([
                            'family_id' => $familyId,
                            'name' => $subfamilyName,
                            'work_cost_points' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
                $subfamilyId = $subfamilyIdByKey[$subKey];

                // --- Upsert category ---
                if (!isset($categoryIdByName[$categoryName])) {
                    $categoryIdByName[$categoryName] =
                        DB::table('categories')->where('name', $categoryName)->value('id')
                        ?? DB::table('categories')->insertGetId([
                            'name' => $categoryName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
                $categoryId = $categoryIdByName[$categoryName];

                // --- Upsert product by external_id ---
                $payload = [
                    'external_id' => $externalId,
                    'name' => $name,
                    'brand_id' => $brandId,
                    'subfamily_id' => $subfamilyId,
                    'category_id' => $categoryId,
                    'model_sku' => $modelSku !== '' ? $modelSku : null,
                    'price_ars' => $price,
                    'description' => $description,
                    'technical_specs' => is_array($technicalSpecs)
                        ? json_encode($technicalSpecs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        : null,

                    'updated_at' => now(),
                ];

                // Insert or update
                $existingId = DB::table('products')->where('external_id', $externalId)->value('id');

                if ($existingId) {
                    DB::table('products')->where('id', $existingId)->update($payload);
                } else {
                    $payload['created_at'] = now();
                    DB::table('products')->insert($payload);
                }
            }
        });

        $this->command?->info('CatalogoSielseSeeder finished successfully.');
    }

    private function requireCol(array $idx, string $name): int
    {
        if (!array_key_exists($name, $idx)) {
            throw new \RuntimeException("Required column '{$name}' not found in header row.");
        }
        return $idx[$name];
    }

    private function optionalCol(array $idx, string $name): ?int
    {
        return array_key_exists($name, $idx) ? $idx[$name] : null;
    }

    private function parseMoney($value): ?string
    {
        if ($value === null)
            return null;

        // Best case: Excel already gave you a number
        if (is_int($value) || is_float($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        $s = trim((string) $value);
        if ($s === '' || strtolower($s) === 'sin_datos')
            return null;

        // Keep only digits, commas, dots, minus
        $s = preg_replace('/[^\d,.\-]/u', '', $s);
        if ($s === '' || $s === '-')
            return null;

        // Your rule: "," thousands and "." decimals
        // => remove ALL commas
        $s = str_replace(',', '', $s);

        // Extra safety: if it has more than one ".", keep the last as decimal, remove the rest
        $dotCount = substr_count($s, '.');
        if ($dotCount > 1) {
            $parts = explode('.', $s);
            $dec = array_pop($parts);
            $s = implode('', $parts) . '.' . $dec;
        }

        if (!is_numeric($s))
            return null;

        // Return normalized decimal text for DECIMAL(14,2)
        return number_format((float) $s, 2, '.', '');
    }
}
