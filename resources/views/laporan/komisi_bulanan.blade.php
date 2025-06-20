<!DOCTYPE html>
<html>

<head>
    <title>ReUse Mart - Komisi Bulanan Per Produk</title>
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
        <p>Jl. Green Eco Park No. 456 Yogyakarta</p>
        <br />
        <h3 class="laporan">LAPORAN KOMISI BULANAN</h3>
        <p>Bulan : {{ $bulan }}</p>
        <p>Tahun : {{ $tahun }}</p>
        <p>Tanggal cetak : {{ $tanggal_cetak }}</p>

        <table>
            <thead>
                <tr>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th>Harga Jual</th>
                    <th>Tanggal Masuk</th>
                    <th>Tanggal Laku</th>
                    <th>Komisi Hunter</th>
                    <th>Komisi Reuse Mart</th>
                    <th>Bonus Penitip</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>K{{ $item['kode_produk'] }}</td>
                        <td>{{ $item['nama_produk'] }}</td>
                        <td>{{ $item['harga_jual'] }}</td>
                        <td>{{ $item['tanggal_masuk'] }}</td>
                        <td>{{ $item['tanggal_laku'] }}</td>
                        <td>{{ number_format($item['komisi_hunter'], 0, ',', '.') }}</td>
                        <td>{{ number_format($item['komisi_reusemart'], 0, ',', '.') }}</td>
                        <td>{{ number_format($item['bonus_penitip'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="text-align: right;"><strong>Total </strong></td>
                    <td>{{ number_format($totalHargaJual, 0, ',', '.') }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ number_format($totalKomisiHunter, 0, ',', '.') }}</td>
                    <td>{{ number_format($totalKomisiReusemart, 0, ',', '.') }}</td>
                    <td>{{ number_format($totalBonusPenitip, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
