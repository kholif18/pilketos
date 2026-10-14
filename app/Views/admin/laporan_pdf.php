<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemilihan</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 30px; }
        h1 { font-size: 26px; margin-bottom: 8px; }
        .meta { color: #475569; margin-bottom: 24px; }
        .summary { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
        .box { border: 1px solid #cbd5e1; border-radius: 10px; padding: 12px 16px; min-width: 140px; }
        .label { font-size: 11px; text-transform: uppercase; color: #475569; }
        .value { font-size: 24px; font-weight: bold; margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 10px 8px; text-align: left; }
        th { background: #eff6ff; }
    </style>
</head>
<body>
    <h1>Laporan Pemilihan</h1>
    <div class="meta">Dicetak pada: <?= esc($generatedAt ?? date('d-m-Y H:i:s')) ?></div>

    <div class="summary">
        <div class="box">
            <div class="label">DPT</div>
            <div class="value"><?= esc($totalPemilih ?? 0) ?></div>
        </div>
        <div class="box">
            <div class="label">Sudah Vote</div>
            <div class="value"><?= esc($totalSudahVote ?? 0) ?></div>
        </div>
        <div class="box">
            <div class="label">Belum Vote</div>
            <div class="value"><?= esc($totalBelumVote ?? 0) ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Calon</th>
                <th>Jumlah Suara</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($hasilVote)): ?>
                <tr>
                    <td colspan="3" style="text-align:center;">Belum ada data.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($hasilVote as $item): ?>
                    <tr>
                        <td><?= esc($item['no'] ?? '-') ?></td>
                        <td><?= esc($item['nama'] ?? '-') ?></td>
                        <td><?= esc($item['jumlah'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
