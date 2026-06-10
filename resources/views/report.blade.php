<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>FinSync Report</title>

<style>

body{
    font-family: DejaVu Sans, sans-serif;
    padding:30px;
}

h1{
    color:#6C3FE8;
    margin:0;
}

.summary{
    margin-top:20px;
}

.box{
    border:1px solid #ddd;
    padding:15px;
    margin-bottom:10px;
}

.label{
    color:#666;
    font-size:12px;
}

.value{
    font-size:22px;
    font-weight:bold;
}

.section-title{
    font-size:18px;
    font-weight:bold;
    margin-top:25px;
    margin-bottom:10px;
    color:#6C3FE8;
}

</style>

</head>

<body>

<div style="text-align:center; margin-bottom:20px;">
    <img
        src="{{ public_path('logo-finsync.jpeg') }}"
        width="120"
        style="margin-bottom:10px;"
    >
    <h1>
        FinSync Monthly Report
    </h1>

</div>

<p>
Generated:
{{ now()->format('d F Y H:i') }}
</p>

<hr>

<div class="section-title">
User Information
</div>

<div class="box">
    <div class="label">Name</div>
    <div class="value">
        {{ $user->first_name }}
        {{ $user->last_name }}
    </div>
</div>

<div class="box">
    <div class="label">Email</div>
    <div class="value">
        {{ $user->email }}
    </div>
</div>

<hr>

<div class="section-title">
Financial Summary (Last 30 Days)
</div>

<div class="box">
    <div class="label">
        Current Balance
    </div>

    <div class="value">
        Rp {{ number_format($balance,0,",",".") }}
    </div>
</div>

<div class="box">
    <div class="label">
        Total Transactions
    </div>

    <div class="value">
        {{ $transactions }}
    </div>
</div>

<div class="box">
    <div class="label">
        Total Topup
    </div>

    <div class="value">
        Rp {{ number_format($topup,0,",",".") }}
    </div>
</div>

<div class="box">
    <div class="label">
        Total Transfer
    </div>

    <div class="value">
        Rp {{ number_format($transfer,0,",",".") }}
    </div>
</div>

<div class="box">
    <div class="label">
        Total Savings
    </div>

    <div class="value">
        Rp {{ number_format($savings,0,",",".") }}
    </div>
</div>

<hr>

<div class="section-title">
Savings Goal Progress
</div>

<div class="box">
    <div class="label">
        Savings Goal
    </div>

    <div class="value">
        Rp {{ number_format($goal,0,",",".") }}
    </div>
</div>

<div class="box">
    <div class="label">
        Savings Progress
    </div>

    <div class="value">
        {{ $progress }}%
    </div>
</div>

</body>
</html>