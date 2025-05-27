<!DOCTYPE html>
<html>

<head>
    <title>ReUse Mart - Stok Gudang</title>
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
        <h3 class="laporan">LAPORAN Stok Gudang</h3>
        <p>Tanggal cetak : {{ $tanggal_cetak }}</p>

        <table>
            <thead>
                <tr>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th>Id Penitip</th>
                    <th>Nama Penitip</th>
                    <th>Tanggal Masuk</th>
                    <th>Perpanjangan</th>
                    <th>Id Hunter</th>
                    <th>Nama Hunter</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>K{{ $item['kode_barang'] }}</td>
                        <td>{{ $item['nama_barang'] }}</td>
                        <td>T{{ $item['id_penitip'] }}</td>
                        <td>{{ $item['nama_penitip'] }}</td>
                        <td>{{ $item['tanggal_masuk'] }}</td>
                        <td>{{ $item['perpanjangan'] }}</td>
                        <td>
                            @if (!empty($item['id_hunter']))
                                P{{ $item['id_hunter'] }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $item['nama_hunter'] }}</td>
                        <td>{{ number_format($item['harga'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
