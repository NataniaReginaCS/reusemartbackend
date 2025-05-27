<!DOCTYPE html>
<html>

<head>
    <title>ReUse Mart - Laporan Penjualan Bulanan Keseluruhan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }
    </style>
</head>

<body>
    <div style="border: 1px solid #000; padding: 20px;">
        <h3>ReUse Mart</h3>
        <p>JL. Green Eco Park No. 456 Yogyakarta</p>
        <h3>LAPORAN PENJUALAN BULANAN</h3>
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
            </tbody>
        </table>
    </div>
    <br />
    <img src="{{ $chartImagePath }}" alt="Sales Chart" style="width: 100%; height: auto;">
</body>

</html>
