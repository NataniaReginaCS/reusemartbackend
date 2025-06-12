<!DOCTYPE html>
<html>
<head>
    <title>ReUse Mart - Donasi Barang</title>
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

        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            clear: both;
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
        <h3 class="laporan">LAPORAN Transaksi Penitip</h3>
        <p>ID Penitip : {{ $id_penitip }}</p>
        <p>Nama Penitip : {{ $nama_penitip }}</p>
        <p>Bulan : {{ $bulan }}</p>
        <p>Tahun : {{ $tahun }}</p>
        <p>Tanggal cetak : {{ $tanggal_cetak }}</p>

        <table>
            <thead>
                <tr>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th>Tanggal Masuk</th>
                    <th>Tanggal Laku</th>
                    <th>Harga Jual Bersih (sudah dipotong Komisi)</th>
                    <th>Bonus terjual cepat</th>
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>{{ $item['kode_barang']}}</td>
                        <td>{{ $item['nama_barang']}}</td>
                        <td>{{ $item['tanggal_masuk']}}</td>
                        <td>{{ $item['tanggal_lunas']}}</td>
                        <td>{{ number_format($item['komisi_penitip'], 0, ',', '.')}}</td>
                        <td>{{ number_format($item['bonus_penitip'], 0, ',', '.') }}</td>
                        <td>{{ number_format($item['pendapatan'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4">TOTAL</td>
                    <td>{{ number_format($totalHargaJual, 0, ',', '.') }}</td>
                    <td>{{ number_format($totalBonus, 0, ',', '.') }}</td>
                    <td>{{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
