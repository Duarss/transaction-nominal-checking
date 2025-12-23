{{-- resources/views/exports/transactions-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 20px; }
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 10px; 
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h2 { 
            margin: 0 0 5px 0; 
            font-size: 16px;
        }
        .header p { 
            margin: 2px 0; 
            font-size: 9px;
        }
        .filters {
            background-color: #f5f5f5;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 9px;
        }
        .filters span {
            font-weight: bold;
            margin-right: 5px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
            font-size: 8px;
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            padding: 6px 4px;
            border: 1px solid #ddd;
            text-align: left;
        }
        td { 
            padding: 5px 4px; 
            border: 1px solid #ddd;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { 
            font-weight: bold; 
            background-color: #e8f4fd;
        }
        .footer { 
            margin-top: 20px; 
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #666;
        }
        .status-sesuai { color: #28a745; }
        .status-kurang { color: #dc3545; }
        .status-lebih { color: #ffc107; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Dibuat pada: {{ $exportDate }}</p>
        <p>Dibuat oleh: {{ $user->name }} ({{ $user->username }})</p>
        @if($user->branch)
            <p>Cabang: {{ $user->branch->name }}</p>
        @endif
    </div>
    
    @if(!empty($filters))
    <div class="filters">
        <strong>Filter:</strong>
        @foreach($filters as $key => $value)
            <div style="display: inline-block; margin-right: 15px;">
                <span>{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> {{ $value }}
            </div>
        @endforeach
    </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Doc ID</th>
                <th width="8%">Tanggal</th>
                <th width="12%">Cabang</th>
                <th width="12%">Sales</th>
                <th width="15%">Pelanggan</th>
                <th width="10%" class="text-right">Total</th>
                <th width="10%" class="text-right">Terbayar</th>
                <th width="10%" class="text-right">Δ</th>
                <th width="5%" class="text-center">Status</th>
                <th width="10%">Metode</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['doc_id'] }}</td>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['branch_name'] }}</td>
                <td>{{ $row['sales_name'] }}</td>
                <td>{{ $row['customer_name'] }}</td>
                <td class="text-right">IDR {{ number_format($row['total'], 0, ',', '.') }}</td>
                <td class="text-right">IDR {{ number_format($row['paid_amount'], 0, ',', '.') }}</td>
                <td class="text-right">IDR {{ number_format($row['discrepancy'], 0, ',', '.') }}</td>
                <td class="text-center">
                    <span class="status-{{ strtolower($row['status']) }}">
                        {{ $row['status'] }}
                    </span>
                </td>
                <td>{{ $row['method'] }}</td>
            </tr>
            @endforeach
            
            @if(isset($totals) && count($data) > 0)
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>IDR {{ number_format($totals['total'], 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>IDR {{ number_format($totals['paid'], 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>IDR {{ number_format($totals['discrepancy'], 0, ',', '.') }}</strong></td>
                <td colspan="2"></td>
            </tr>
            @endif
        </tbody>
    </table>
    
    <div class="footer">
        <p>Jumlah Data: {{ count($data) }} transaksi</p>
        <p>Sistem Transaction Nominal Checking - {{ config('app.name', 'Laravel') }}</p>
    </div>
</body>
</html>