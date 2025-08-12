<?php
// ----- Your original PHP logic unchanged -----
function get_client_ip() {
    $headers = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED'
    ];
    foreach ($headers as $h) {
        if (!empty($_SERVER[$h])) {
            $ips = explode(',', $_SERVER[$h]);
            foreach ($ips as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}
function get_client_port() { return $_SERVER['REMOTE_PORT'] ?? ''; }
function get_local_ip() {
    $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock === false) return gethostbyname(gethostname());
    @socket_connect($sock, '8.8.8.8', 53);
    @socket_getsockname($sock, $local_ip, $port);
    @socket_close($sock);
    return !empty($local_ip) ? $local_ip : gethostbyname(gethostname());
}
function get_server_id() {
    $id = getenv('SERVER_ID');
    if ($id) return $id;
    $file = '/etc/server_id';
    if (is_readable($file)) {
        $content = trim(file_get_contents($file));
        if ($content !== '') return $content;
    }
    return gethostname();
}
function get_server_addr() {
    if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '::1') {
        return $_SERVER['SERVER_ADDR'];
    }
    return get_local_ip();
}
function get_backend_port() {
    $env = getenv('BACKEND_PORT');
    if ($env !== false && trim($env) !== '' && is_numeric($env)) return trim($env);
    $file = '/etc/server_port';
    if (is_readable($file)) {
        $content = trim(file_get_contents($file));
        if ($content !== '' && is_numeric($content)) return $content;
    }
    return $_SERVER['SERVER_PORT'] ?? '80';
}

$is_https = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
$banner_color = $is_https ? '#c0392b' : '#0071c5';
$client_ip = get_client_ip();
$client_port = get_client_port();
$client_display = $client_ip . ($client_port ? ':' . $client_port : '');
$server_id = get_server_id();
$server_addr = get_server_addr();
$server_port = get_backend_port();
$server_display = "Server {$server_id} ({$server_addr}:{$server_port})";
$virtual_host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? $server_addr);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>F5 Training Services - Demo</title>
<style>
:root { --dark:#2d2d2d; --muted:#f2f2f2; }
body { margin:0; font-family: Arial, Helvetica, sans-serif; color:#333; background:#fff; }
.header { background:var(--dark); color:#fff; display:flex; align-items:center; gap:12px; padding:10px 20px;}
.header img{height:36px}
.title {font-weight:600; font-size:18px; flex:1}
.toplinks {background:#073a6b;color:#fff;padding:6px 18px;font-size:13px;display:flex;justify-content:flex-end}
.toplinks a{color:#fff;margin-left:12px;text-decoration:underline}
.banner{color:#fff;padding:30px 24px;text-align:center}
.banner .meta{font-size:15px;margin:6px 0}
.banner .chip{background:rgba(255,255,255,0.95);color:#222;padding:3px 8px;border-radius:4px;font-weight:600;}
.banner h1{font-size:80px;margin:10px 0 0;font-weight:700;opacity:0.95}

.circle-container {
    display: flex;
    justify-content: center;
    gap: 50px;
    margin: 40px 0;
}
.circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background-color: white;
    border: 8px solid #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    transition: transform 0.3s ease, border-color 0.3s ease;
}
.circle img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.circle:hover {
    transform: scale(1.05);
    border-color: #007acc;
}

.container{max-width:920px;margin:24px auto;padding:0 18px;color:#444}
h3{margin-top:18px;color:#222} p{line-height:1.5;font-size:14px}
.footer{background:var(--muted);padding:22px;display:flex;gap:24px;justify-content:center}
.footer .col{max-width:260px;font-size:13px;color:#333}
.footer .col strong{display:block;margin-bottom:6px}
@media (max-width:720px){ 
    .circle-container{flex-direction:column;gap:18px;align-items:center} 
    .banner h1{font-size:48px} 
}
</style>
</head>
<body>

<div class="header">
  <img src="https://d1hbpr09pwz0sk.cloudfront.net/logo_url/tirzok-private-limited-a336816e" alt="F5">
  <div class="title">F5 Training Services</div>
  <div style="font-size:13px;color:#cde9ff;">
    HTTP to <strong><?=htmlspecialchars($virtual_host)?></strong>
  </div>
</div>

<div class="toplinks">
  <div style="margin-right:auto"></div>
  <a href="#">Source IP Address</a>
</div>

<div class="banner" style="background:<?=$banner_color?>;">
  <div class="meta">
    HTML served from <strong><?=htmlspecialchars($server_display)?></strong>
  </div>
  <div class="meta" style="margin-top:4px;">
    Client IP address: <span class="chip"><?=htmlspecialchars($client_display)?></span>
  </div>
  <h1><?=htmlspecialchars($server_id)?></h1>
</div>

<!-- New two-image circle section -->
<div class="circle-container">
    <a href="https://f5.com" target="_blank" class="circle">
        <img src="https://www.f5.com/content/dam/f5/f5-logo.svg" alt="F5 Logo">
    </a>
    <a href="https://tirzok.com" target="_blank" class="circle">
        <img src="https://softexpo.com.bd/assets/media/exhibitor_logo/1661768060.png" alt="Tirzok Logo">
    </a>
</div>

<div class="container">
  <h3>About This Application</h3>
       <p>
      As with any HTTP application, this page is comprised of a number of different elements. First, there's the HTML that drives the whole page. There are graphical elements, too, in the form of some JPG and PNG images. The page layout is controlled with a cascading style sheet (CSS).
    </p>
    <p>
      Each element is requested from the client to the BIG-IP system and then from the BIG-IP system to the back end servers. These connections are load balanced across the available pool members associated with the virtual server you connected to.
    </p>
    <p>
      The HTML was served from the pool member at <strong><?php echo htmlspecialchars($server_addr . ':' . $server_port); ?></strong>. The client's IP address, as seen by the server, is <strong><?php echo htmlspecialchars($client_ip); ?></strong>.
    </p>
    <p>
      As you change various settings on your virtual server and pool configurations, some of the values shown here may also change.
    </p>
</div>

<div class="footer">
  <div class="col">
    <strong>Md. Anower Perves</strong>
    Team Lead, DevOps<br>
    Tirzok Private Limited<br>
    Email: anower@tirzok.com<br>
    Phone: +8801870749007
  </div>
  <div class="col">
    <strong>Tirzok Private Limited</strong>
    House#72, Road#03,<br>
    Block#B, Niketan, Gulshan,<br> 
    Dhaka-1212, Bangladesh.<br>
  </div>
</div>

</body>
</html>
 
