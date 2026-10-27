<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Rapatku')</title>
<link rel="icon" type="image/png" href="{{ $appLogo }}">
<link rel="shortcut icon" href="{{ $appLogo }}">
<link rel="apple-touch-icon" href="{{ $appLogo }}">
<link rel="manifest" href="{{ route('manifest') }}">
<meta name="theme-color" content="#0F2F5B">
<script src="{{ asset('js/tailwindcdn.js') }}"></script>
<style>
  :root{
    --bnn: #174A9E;
    --bnn-dark: #0F2F5B;
    --bnn-soft: #EDF2F9;
    --bnn-gold: #E9A23B;
  }
  body{ font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Inter, Arial, sans-serif; }
  .tabular{ font-variant-numeric: tabular-nums; }
  .card{ background:#fff; border:1px solid #e6ecf5; border-radius:1.5rem; box-shadow:0 1px 2px rgba(15,47,91,.06); }
  .btn-bnn{ background:var(--bnn); color:#fff; font-weight:600; border-radius:.8rem; padding:.55rem 1rem; display:inline-flex; align-items:center; gap:.5rem; }
  .btn-bnn:hover{ background:var(--bnn-dark); }
  .btn-soft{ background:#e3eaf6; color:var(--bnn-dark); font-weight:600; border-radius:.8rem; padding:.55rem 1rem; display:inline-flex; align-items:center; gap:.5rem; }
  .btn-soft:hover{ background:#d3ddf0; }
  .input-bnn{ width:100%; border:1px solid #d7e0ef; border-radius:.8rem; padding:.6rem .8rem; background:#fff; }
  .input-bnn:focus{ outline:none; border-color:var(--bnn); box-shadow:0 0 0 3px rgba(23,74,158,.15); }
  .side-link {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1.1rem;
    margin-right: .85rem;
    border-radius: 9999px;
    font-size: .8125rem;
    font-weight: 600;
    color: #cbd5e1;
    transition: background-color .15s ease, color .15s ease;
    position: relative;
    text-decoration: none;
  }
  .side-link:hover {
    background: rgba(255,255,255,.08);
    color: #fff;
  }
  .side-link.active {
    background: #F6F8FC;
    color: #0F2F5B;
    margin-right: 0;
    padding-right: 1.5rem;
    border-radius: 9999px 0 0 9999px;
    font-weight: 700;
    z-index: 10;
  }
  .side-link.active::before {
    content: "";
    position: absolute;
    top: -24px;
    right: 0;
    width: 24px;
    height: 24px;
    background: transparent;
    border-bottom-right-radius: 24px;
    box-shadow: 10px 10px 0 10px #F6F8FC;
    pointer-events: none;
  }
  .side-link.active::after {
    content: "";
    position: absolute;
    bottom: -24px;
    right: 0;
    width: 24px;
    height: 24px;
    background: transparent;
    border-top-right-radius: 24px;
    box-shadow: 10px -10px 0 10px #F6F8FC;
    pointer-events: none;
  }
</style>
@stack('head')
</head>
<body class="bg-[#EDF2F9] text-slate-800 min-h-screen antialiased">
@yield('content')
@stack('scripts')
@include('components.modal-feedback')
</body>
</html>
