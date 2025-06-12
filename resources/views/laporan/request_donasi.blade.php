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
        <h3 class="laporan">LAPORAN Donasi Barang</h3>
        <p>Tanggal cetak : {{ $tanggal_cetak }}</p>

        <table>
            <thead>
                <tr>
                    <th>ID Organisasi</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>Request</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>ORG{{ $item['id_organisasi'] }}</td>
                        <td>{{ $item['nama_organisasi'] }}</td>
                        <td>T{{ $item['alamat_organisasi'] }}</td>
                        <td>{{ $item['request'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
