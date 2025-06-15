<!DOCTYPE html>
<html>

<head>
    <title>ReUse Mart - Laporan Penjualan Bulanan Keseluruhan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #2_clear: both;
        }

        .laporan {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div style="border: 1px solid #000; padding: 20px;">
        <h3>ReUse Mart</h3>
        <p>JL. Green Eco Park No. 456 Yogyakarta</p>
        <br />
        <h3 class="laporan">LAPORAN PENJUALAN BULANAN</h3>
        <p>Tahun : {{ $year }}</p>
        <p>Tanggal cetak : {{ $tanggal_cetak }}</p>
        <table class="table">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Jumlah Barang Terjual</th>
                    <th>Jumlah Penjualan Kotor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($penjualanData as $data)
                    <tr>
                        <td>{{ $data['month'] }}</td>
                        <td>{{ $data['items'] }}</td>
                        <td>{{ number_format($data['sales'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="text-align: right;"><strong>Total </strong></td>
                    <td>{{ number_format($jumlahKotor, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <img src="{{ $chartImagePath }}" alt="Sales Chart" style="width: 100%; height: auto;">
</body>

</html>
