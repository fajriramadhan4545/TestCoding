<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Servis Selesai</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #1a56db;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
        }
        .badge {
            display: inline-block;
            background-color: #22c55e;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
        }
        .content {
            padding: 30px;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 15px 0;
        }
        .info-card h3 {
            margin: 0 0 15px 0;
            color: #374151;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            color: #6b7280;
            font-weight: 500;
        }
        .value {
            color: #111827;
            font-weight: 600;
        }
        .alert-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 0 6px 6px 0;
        }
        .alert-box p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚢 Ship Maintenance System</h1>
            <span class="badge">✓ SERVIS SELESAI</span>
        </div>

        <div class="content">
            <p style="color: #374151; font-size: 15px;">
                Kepada <strong>Manajer Operasional</strong>,
            </p>
            <p style="color: #6b7280; font-size: 14px; line-height: 1.6;">
                Sistem telah mendeteksi perubahan status servis menjadi <strong>Completed</strong>.
                Berikut adalah ringkasan informasi servis yang telah selesai beserta jadwal servis berikutnya.
            </p>

            <div class="info-card">
                <h3>📋 Servis yang Diselesaikan</h3>
                <div class="info-row">
                    <span class="label">Nama Kapal</span>
                    <span class="value">{{ $completedLog->ship->nama ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Kode Kapal</span>
                    <span class="value">{{ $completedLog->ship->kode_kapal ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Jenis Servis</span>
                    <span class="value">{{ $completedLog->jenis_servis }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Tanggal Servis</span>
                    <span class="value">{{ \Carbon\Carbon::parse($completedLog->tanggal_servis)->format('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Total Biaya</span>
                    <span class="value">Rp {{ number_format($completedLog->biaya, 0, ',', '.') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value" style="color: #22c55e;">✓ Completed</span>
                </div>
            </div>

            <div class="info-card">
                <h3>📅 Jadwal Servis Berikutnya (Otomatis Terjadwal)</h3>
                <div class="info-row">
                    <span class="label">Jenis Servis</span>
                    <span class="value">{{ $scheduledLog->jenis_servis }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Tanggal Rencana</span>
                    <span class="value">{{ \Carbon\Carbon::parse($scheduledLog->tanggal_servis)->format('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Estimasi Biaya</span>
                    <span class="value">Rp {{ number_format($scheduledLog->biaya, 0, ',', '.') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value" style="color: #3b82f6;">📌 Planned</span>
                </div>
            </div>

            <div class="alert-box">
                <p>⚠️ <strong>Perhatian:</strong> Jadwal servis rutin berikutnya telah otomatis dijadwalkan 6 bulan setelah tanggal servis terakhir. Pastikan anggaran dan sumber daya telah disiapkan.</p>
            </div>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh <strong>Ship Maintenance System</strong>.</p>
            <p>Harap tidak membalas email ini.</p>
            <p style="margin-top: 10px;">© {{ date('Y') }} Ship Maintenance API. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
