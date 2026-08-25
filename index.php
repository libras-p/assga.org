<?php
// ============================================================
// CONFIGURAÇÃO DO BANCO DE DADOS
// ============================================================
$host = getenv('ASSGA_DB_HOST') ?: '127.0.0.1';
$db   = getenv('ASSGA_DB_NAME') ?: 'assga_db';
$user = getenv('ASSGA_DB_USER') ?: 'assga_app';
$pass = getenv('ASSGA_DB_PASSWORD') ?: 'assga_app_2026';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro no banco: " . $e->getMessage());
}

// ============================================================
// CRIAÇÃO DAS TABELAS (se não existirem)
// ============================================================
$sql = "
CREATE TABLE IF NOT EXISTS noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    imagem VARCHAR(255),
    data VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    data_inicio VARCHAR(20),
    data_fim VARCHAR(20),
    local VARCHAR(255),
    vagas INT,
    valor DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'aberto'
);

CREATE TABLE IF NOT EXISTS diretoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    descricao TEXT,
    email VARCHAR(255),
    telefone VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS estatuto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conteudo LONGTEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS inscricoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vaga INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(20) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    cupom VARCHAR(50),
    pagamento VARCHAR(50),
    status_pagamento VARCHAR(20) DEFAULT 'NAO PAGO',
    valor DECIMAL(10,2),
    data VARCHAR(30),
    codigo VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    senha VARCHAR(255) NOT NULL,
    nome_associacao VARCHAR(255),
    endereco VARCHAR(255),
    email VARCHAR(255),
    telefone VARCHAR(20),
    cnpj VARCHAR(20)
);

INSERT IGNORE INTO config (id, senha, nome_associacao, endereco, email, telefone, cnpj)
VALUES (1, 'ASSGA2026', 'ASSGA - Associação Desportiva', 'São Gonçalo do Amarante - RN', 'assgar2019@gmail.com', '(84) 99698-1248', '57.242.499/0001-60');
";

$pdo->exec($sql);

// ============================================================
// FUNÇÕES DE ACESSO AOS DADOS
// ============================================================
function obterDados($pdo, $tabela, $campos = '*', $where = '') {
    $sql = "SELECT $campos FROM $tabela $where";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function inserir($pdo, $tabela, $dados) {
    $campos = implode(',', array_keys($dados));
    $valores = ':' . implode(',:', array_keys($dados));
    $sql = "INSERT INTO $tabela ($campos) VALUES ($valores)";
    $stmt = $pdo->prepare($sql);
    foreach ($dados as $k => $v) {
        $stmt->bindValue(":$k", $v);
    }
    return $stmt->execute();
}

function atualizar($pdo, $tabela, $dados, $id) {
    $sets = [];
    foreach ($dados as $k => $v) {
        $sets[] = "$k = :$k";
    }
    $sql = "UPDATE $tabela SET " . implode(',', $sets) . " WHERE id = :id";
    $dados['id'] = $id;
    $stmt = $pdo->prepare($sql);
    foreach ($dados as $k => $v) {
        $stmt->bindValue(":$k", $v);
    }
    return $stmt->execute();
}

function excluir($pdo, $tabela, $id) {
    $sql = "DELETE FROM $tabela WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id);
    return $stmt->execute();
}

// ============================================================
// ROTAS DA API (requisições AJAX)
// ============================================================
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    $acao = $_GET['api'];

    // ---------- NOTÍCIAS ----------
    if ($acao === 'noticias') {
        echo json_encode(obterDados($pdo, 'noticias', '*', 'ORDER BY id DESC'));
        exit;
    }
    if ($acao === 'salvar_noticia') {
        $dados = json_decode(file_get_contents('php://input'), true);
        if (isset($dados['id']) && $dados['id']) {
            atualizar($pdo, 'noticias', $dados, $dados['id']);
        } else {
            inserir($pdo, 'noticias', $dados);
        }
        echo json_encode(['status' => 'ok']);
        exit;
    }
    if ($acao === 'excluir_noticia') {
        $id = $_GET['id'];
        excluir($pdo, 'noticias', $id);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    // ---------- EVENTOS ----------
    if ($acao === 'eventos') {
        echo json_encode(obterDados($pdo, 'eventos', '*', 'ORDER BY id DESC'));
        exit;
    }
    if ($acao === 'salvar_evento') {
        $dados = json_decode(file_get_contents('php://input'), true);
        if (isset($dados['id']) && $dados['id']) {
            atualizar($pdo, 'eventos', $dados, $dados['id']);
        } else {
            inserir($pdo, 'eventos', $dados);
        }
        echo json_encode(['status' => 'ok']);
        exit;
    }
    if ($acao === 'excluir_evento') {
        $id = $_GET['id'];
        excluir($pdo, 'eventos', $id);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    // ---------- DIRETORIA ----------
    if ($acao === 'diretoria') {
        echo json_encode(obterDados($pdo, 'diretoria', '*', 'ORDER BY id'));
        exit;
    }
    if ($acao === 'salvar_membro') {
        $dados = json_decode(file_get_contents('php://input'), true);
        if (isset($dados['id']) && $dados['id']) {
            atualizar($pdo, 'diretoria', $dados, $dados['id']);
        } else {
            inserir($pdo, 'diretoria', $dados);
        }
        echo json_encode(['status' => 'ok']);
        exit;
    }
    if ($acao === 'excluir_membro') {
        $id = $_GET['id'];
        excluir($pdo, 'diretoria', $id);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    // ---------- ESTATUTO ----------
    if ($acao === 'estatuto') {
        $res = obterDados($pdo, 'estatuto');
        echo json_encode($res);
        exit;
    }
    if ($acao === 'salvar_estatuto') {
        $dados = json_decode(file_get_contents('php://input'), true);
        if (isset($dados['id']) && $dados['id']) {
            atualizar($pdo, 'estatuto', $dados, $dados['id']);
        } else {
            inserir($pdo, 'estatuto', $dados);
        }
        echo json_encode(['status' => 'ok']);
        exit;
    }

    // ---------- INSCRIÇÕES ----------
    if ($acao === 'inscricoes') {
        echo json_encode(obterDados($pdo, 'inscricoes', '*', 'ORDER BY id DESC'));
        exit;
    }
    if ($acao === 'salvar_inscricao') {
        $dados = json_decode(file_get_contents('php://input'), true);
        // Gera código único
        $dados['codigo'] = 'ASSGA-' . time();
        $dados['data'] = date('d/m/Y H:i:s');
        inserir($pdo, 'inscricoes', $dados);
        echo json_encode(['status' => 'ok']);
        exit;
    }
    if ($acao === 'alterar_pagamento') {
        $id = $_GET['id'];
        $status = $_GET['status'];
        $pdo->prepare("UPDATE inscricoes SET status_pagamento = ? WHERE id = ?")->execute([$status, $id]);
        echo json_encode(['status' => 'ok']);
        exit;
    }
    if ($acao === 'excluir_inscricao') {
        $id = $_GET['id'];
        excluir($pdo, 'inscricoes', $id);
        echo json_encode(['status' => 'ok']);
        exit;
    }
    if ($acao === 'resetar_inscricoes') {
        $pdo->exec("TRUNCATE inscricoes");
        echo json_encode(['status' => 'ok']);
        exit;
    }

    // ---------- CONFIG ----------
    if ($acao === 'config') {
        echo json_encode(obterDados($pdo, 'config'));
        exit;
    }
    if ($acao === 'salvar_config') {
        $dados = json_decode(file_get_contents('php://input'), true);
        atualizar($pdo, 'config', $dados, $dados['id']);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    // ---------- LOGIN ----------
    if ($acao === 'login') {
        $dados = json_decode(file_get_contents('php://input'), true);
        $senha = $dados['senha'];
        $config = obterDados($pdo, 'config');
        $config = $config[0] ?? [];
        $senhaSalva = $config['senha'] ?? 'ASSGA2026';
        if ($senha === $senhaSalva) {
            echo json_encode(['status' => 'ok', 'admin' => true]);
        } else {
            echo json_encode(['status' => 'erro', 'msg' => 'Senha incorreta']);
        }
        exit;
    }

    echo json_encode(['status' => 'erro', 'msg' => 'Ação inválida']);
    exit;
}

// ============================================================
// CARREGAR HTML (front-end)
// ============================================================
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASSGA - Associação Desportiva</title>
    <link rel="icon" href="src/imagens/Assga_foto.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* (copie todo o CSS da versão anterior aqui) */
        /* Por brevidade, omitimos o CSS, mas você deve incluir todo o estilo da versão anterior */
        /* ====================================================
           RESET E BASE (versão resumida para exemplo)
           ==================================================== */
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
        body { background:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%); color:#333; min-height:100vh; display:flex; flex-direction:column; }
        h2 { text-align:center; margin:45px 0 35px; color:#003366; font-size:2.5em; font-weight:800; text-shadow:2px 2px 4px rgba(0,0,0,.1); }
        h2 i { color:#004aad; margin-right:10px; }
        .container { flex:1; max-width:1300px; margin:0 auto; padding:20px; width:100%; }
        .secao { display:none; animation:fadeIn 0.5s ease; }
        .secao.ativa { display:block; }
        @keyframes fadeIn { from{opacity:0;transform:translateY(20px);} to{opacity:1;transform:translateY(0);} }
        /* ====================================================
           HEADER
           ==================================================== */
        header { background:linear-gradient(135deg,#003366 0%,#004aad 100%); color:#fff; display:flex; justify-content:space-between; align-items:center; padding:15px 30px; position:sticky; top:0; z-index:9999; box-shadow:0 8px 32px rgba(0,51,102,.3); flex-wrap:wrap; }
        .logo-area { display:flex; align-items:center; gap:15px; cursor:pointer; transition:transform .3s; }
        .logo-area:hover { transform:scale(1.05); }
        .logo-area img { width:56px; height:68px; object-fit:cover; border-radius:8px; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,.2); }
        .logo { font-size:28px; font-weight:900; letter-spacing:2px; background:linear-gradient(135deg,#ffd700,#ffed4e); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .menu-toggle { display:none; width:45px; height:45px; align-items:center; justify-content:center; font-size:25px; cursor:pointer; border:0; border-radius:10px; color:#fff; background:transparent; }
        .menu-toggle:hover { background:rgba(255,255,255,.12); }
        .menu { list-style:none; display:inline-grid; grid-auto-flow:column; gap:10px; align-items:center; margin:0; padding:0; }
        .menu a { display:block; color:#fff; text-decoration:none; font-size:15px; font-weight:700; padding:12px 17px; border-radius:25px; transition:all .4s; cursor:pointer; }
        .menu a i { margin-right:7px; color:#fff; transition:.3s; }
        .menu a:hover { color:#003366; background:linear-gradient(135deg,#ffd700,#ffed4e); box-shadow:0 8px 24px rgba(255,215,0,.4); transform:translateY(-3px); }
        .menu a:hover i { color:#003366; }
        /* ====================================================
           SLIDER
           ==================================================== */
        .slider { width:100%; height:560px; overflow:hidden; position:relative; background:#003366; box-shadow:0 10px 35px rgba(0,0,0,.25); border-radius:12px; margin-bottom:30px; }
        .slider img { width:100%; height:100%; object-fit:cover; display:block; transition:opacity .5s; }
        .slider-info { position:absolute; bottom:25px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,.6); backdrop-filter:blur(8px); color:#fff; padding:12px 24px; border-radius:30px; font-size:15px; font-weight:700; z-index:5; text-align:center; white-space:nowrap; box-shadow:0 5px 20px rgba(0,0,0,.25); }
        .slider-info i { color:#ffd700; margin-right:6px; }
        /* ====================================================
           NOTÍCIAS, CARD, ETC (mesmo CSS anterior)
           ==================================================== */
        /* (Inclua todo o CSS da versão anterior aqui) */
        /* Por questões de espaço, estou resumindo, mas você deve usar o CSS completo */
        .noticia-item { background:linear-gradient(135deg,#fff,#f8f9fa); display:flex; gap:35px; align-items:center; margin-bottom:35px; padding:30px; border-radius:20px; box-shadow:0 10px 35px rgba(0,0,0,.12); transition:all .4s; border-left:6px solid #004aad; overflow:hidden; }
        .noticia-item:hover { transform:translateY(-8px); box-shadow:0 18px 50px rgba(0,74,173,.22); border-left-color:#ffd700; }
        .noticia-imagem { flex-shrink:0; width:390px; height:280px; overflow:hidden; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.2); }
        .noticia-imagem img { width:100%; height:100%; object-fit:cover; transition:transform .5s; }
        .noticia-imagem:hover img { transform:scale(1.08); }
        .texto { flex:1; }
        .texto h3 { color:#003366; margin-bottom:12px; font-size:2em; font-weight:900; }
        .texto h3 i { color:#004aad; margin-right:8px; }
        .texto data { display:block; color:#004aad; font-size:14px; font-weight:700; margin-bottom:18px; }
        .texto data i { color:#003366; margin-right:5px; }
        .texto p { line-height:1.7; color:#555; margin:10px 0; font-size:15px; }
        .card { background:#fff; border-radius:20px; padding:30px; margin-bottom:30px; box-shadow:0 10px 35px rgba(0,0,0,.1); border-left:6px solid #004aad; transition:all .4s; }
        .card:hover { transform:translateY(-5px); box-shadow:0 18px 50px rgba(0,74,173,.2); border-left-color:#ffd700; }
        .card h3 { color:#003366; font-size:1.5em; margin-bottom:15px; }
        .card h3 i { color:#004aad; margin-right:8px; }
        .card p { font-size:1.05em; line-height:1.8; color:#555; margin-bottom:10px; }
        .card ul { list-style:none; padding:0; }
        .card ul li { padding:10px 15px; margin-bottom:8px; background:#f0f4fa; border-radius:10px; color:#444; font-size:1em; transition:all .3s; border-left:4px solid transparent; }
        .card ul li:hover { background:#e8eef6; border-left-color:#ffd700; transform:translateX(5px); }
        .card ul li i { color:#004aad; margin-right:10px; width:20px; }
        .page-banner { background:linear-gradient(135deg,#003366,#004aad); padding:60px 20px; text-align:center; color:#fff; border-radius:12px; margin-bottom:30px; }
        .page-banner h1 { font-size:3em; font-weight:900; margin-bottom:10px; }
        .page-banner h1 i { color:#ffd700; margin-right:15px; }
        .page-banner p { font-size:1.2em; opacity:.9; max-width:700px; margin:0 auto; }
        .badge { display:inline-block; padding:4px 14px; border-radius:20px; font-size:.8em; font-weight:700; }
        .status-aberto { background:#d4edda; color:#155724; }
        .status-fechado { background:#f8d7da; color:#721c24; }
        .status-andamento { background:#fff3cd; color:#856404; }
        .evento-grid-info { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px; margin:15px 0; }
        .evento-grid-info .info-item { background:#f0f4fa; padding:10px 15px; border-radius:10px; font-size:.95em; color:#444; }
        .evento-grid-info .info-item i { color:#004aad; margin-right:6px; width:20px; }
        .botao-inscricao { display:inline-flex; align-items:center; gap:10px; background:linear-gradient(135deg,#003366,#004aad 55%,#0066cc); color:#fff; padding:14px 28px; border-radius:30px; text-decoration:none; font-weight:800; border:2px solid #ffd700; box-shadow:0 8px 20px rgba(0,51,102,.3); transition:all .3s; cursor:pointer; margin-top:15px; }
        .botao-inscricao:hover { transform:translateY(-4px) scale(1.02); box-shadow:0 12px 30px rgba(0,74,173,.4); }
        .botao-inscricao i { color:#ffd700; }
        .diretoria-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:30px; margin-top:30px; }
        .membro-card { background:#fff; border-radius:20px; padding:30px 25px; text-align:center; box-shadow:0 10px 35px rgba(0,0,0,.1); transition:all .4s; border-bottom:5px solid #004aad; }
        .membro-card:hover { transform:translateY(-8px); box-shadow:0 18px 50px rgba(0,74,173,.2); border-bottom-color:#ffd700; }
        .membro-card .avatar { width:100px; height:100px; border-radius:50%; margin:0 auto 15px; background:linear-gradient(135deg,#003366,#004aad); display:flex; align-items:center; justify-content:center; color:#ffd700; font-size:3em; box-shadow:0 8px 20px rgba(0,51,102,.2); }
        .membro-card h3 { color:#003366; font-size:1.4em; margin-bottom:5px; }
        .membro-card .cargo { color:#004aad; font-weight:700; font-size:1em; display:block; margin-bottom:8px; }
        .membro-card .cargo i { margin-right:5px; }
        .membro-card p { color:#666; font-size:.95em; line-height:1.5; }
        .membro-card .contato { margin-top:15px; display:flex; justify-content:center; gap:12px; }
        .membro-card .contato a { width:40px; height:40px; display:flex; align-items:center; justify-content:center; color:#fff; background:#004aad; border-radius:50%; font-size:18px; text-decoration:none; transition:all .3s; }
        .membro-card .contato a:hover { background:#ffd700; color:#003366; transform:scale(1.15); }
        .preco-evento { display:inline-block; margin-top:18px; padding:13px 22px; background:linear-gradient(135deg,#fff8d6,#fff1a8); border:2px solid #ffd700; border-radius:12px; color:#6b5200; font-size:19px; font-weight:900; box-shadow:0 5px 15px rgba(255,215,0,.2); }
        .preco-evento i { margin-right:7px; }
        .info { background:linear-gradient(135deg,#e9eef5 0%,#d4e1ed 100%); padding:50px 20px; margin-top:40px; border-radius:12px; }
        .info-container { display:flex; justify-content:center; gap:25px; flex-wrap:wrap; max-width:1200px; margin:auto; }
        .info-card { width:250px; background:linear-gradient(135deg,#fff,#f8f9fa); padding:28px 22px; border-radius:16px; text-align:center; box-shadow:0 8px 24px rgba(0,0,0,.1); transition:all .4s; border-top:4px solid #004aad; }
        .info-card:hover { transform:translateY(-10px); box-shadow:0 16px 48px rgba(0,74,173,.2); border-top-color:#ffd700; }
        .info-card .icon { width:65px; height:65px; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; border-radius:50%; background:linear-gradient(135deg,#003366,#004aad); color:#ffd700; font-size:28px; box-shadow:0 8px 20px rgba(0,51,102,.25); transition:.4s; }
        .info-card:hover .icon { transform:scale(1.15) rotate(8deg); }
        .info-card h3 { color:#003366; margin:12px 0; font-size:1.3em; }
        .info-card h3 i { color:#004aad; margin-right:6px; }
        .info-card p { color:#666; line-height:1.5; }
        .info-card a { display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; color:#fff; background:#004aad; margin-top:15px; border-radius:50%; font-size:18px; text-decoration:none; transition:all .3s; }
        .info-card a:hover { color:#003366; background:#ffd700; transform:scale(1.15); }
        footer { background:linear-gradient(135deg,#003366 0%,#002244 100%); color:#fff; padding:35px 20px; margin-top:50px; }
        .footer-container { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; max-width:1200px; margin:auto; }
        .footer-copy { font-size:13px; line-height:1.8; opacity:.9; }
        .footer-copy i { color:#ffd700; margin-right:5px; }
        .footer-redes { display:flex; align-items:center; gap:10px; }
        .footer-redes a { width:45px; height:45px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:21px; border-radius:50%; background:rgba(255,255,255,.1); text-decoration:none; transition:all .3s; }
        .footer-redes a:hover { color:#003366; background:#ffd700; transform:translateY(-5px) scale(1.1); }
        .link-admin { color:#ffd700; text-decoration:none; font-size:12px; opacity:.6; transition:opacity .3s; margin-left:10px; cursor:pointer; }
        .link-admin:hover { opacity:1; color:#fff; }

        /* ====================================================
           PAINEL ADMIN (CSS resumido)
           ==================================================== */
        .login-box { max-width:400px; margin:40px auto; background:#fff; padding:30px; border-radius:18px; box-shadow:0 15px 45px rgba(0,0,0,.18); text-align:center; }
        .login-box h1 { color:#003366; margin-bottom:25px; }
        .login-box input { width:100%; padding:14px; border:1px solid #ccc; border-radius:8px; font-size:16px; margin-bottom:10px; }
        .login-box button { width:100%; padding:14px; border:0; border-radius:8px; background:#003366; color:#fff; font-size:16px; font-weight:bold; cursor:pointer; }
        .login-box button:hover { background:#004aad; }
        .erro-login { color:#b00000; display:none; margin-top:10px; }
        .painel-header { background:linear-gradient(135deg,#003366,#004aad); color:#fff; padding:20px; border-radius:15px; display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; margin-bottom:20px; }
        .painel-header h1 { margin:0; font-size:1.8em; }
        .painel-header .admin-info { display:flex; align-items:center; gap:15px; }
        .painel-header .admin-info span { background:#ffd700; color:#003366; padding:8px 16px; border-radius:30px; font-weight:700; }
        .btn-sair { background:#d00000; color:#fff; border:0; padding:10px 18px; border-radius:8px; cursor:pointer; font-weight:bold; }
        .admin-tabs { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px; background:#fff; padding:10px; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,.08); }
        .admin-tabs button { padding:10px 18px; border:0; border-radius:8px; background:#e9eef5; color:#333; font-weight:600; cursor:pointer; transition:.3s; flex:1 1 auto; }
        .admin-tabs button:hover { background:#d4e1ed; }
        .admin-tabs button.ativo { background:#003366; color:#fff; }
        .admin-painel-conteudo { background:#fff; border-radius:12px; padding:25px; box-shadow:0 5px 20px rgba(0,0,0,.08); }
        .admin-painel-conteudo .form-group { margin-bottom:15px; }
        .admin-painel-conteudo label { display:block; font-weight:600; color:#003366; margin-bottom:5px; }
        .admin-painel-conteudo input, .admin-painel-conteudo textarea, .admin-painel-conteudo select { width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px; }
        .admin-painel-conteudo textarea { min-height:100px; resize:vertical; }
        .admin-painel-conteudo button[type="submit"] { background:#003366; color:#fff; border:0; padding:12px 25px; border-radius:8px; font-weight:bold; cursor:pointer; transition:.3s; }
        .admin-painel-conteudo button[type="submit"]:hover { background:#004aad; }
        .admin-lista { margin-top:20px; }
        .admin-item { background:#f8f9fa; padding:15px; border-radius:10px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; border-left:4px solid #004aad; }
        .admin-item .info { flex:1; }
        .admin-item .info strong { color:#003366; }
        .admin-item .acoes button { border:0; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:600; margin:3px; }
        .btn-editar { background:#ffc107; color:#333; }
        .btn-excluir-admin { background:#d00000; color:#fff; }
        .admin-mensagem { display:none; padding:12px; border-radius:8px; margin-bottom:15px; font-weight:600; }
        .admin-mensagem.sucesso { background:#d9fbe5; color:#08752b; }
        .admin-mensagem.erro { background:#ffe0e0; color:#a00000; }
        .btn-pagamento { background:#08752b; color:#fff; border:0; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:bold; margin:3px; }
        .btn-pagamento.nao-pago { background:#d97706; }
        .card-resumo { background:#fff; padding:15px; border-radius:10px; text-align:center; box-shadow:0 5px 20px rgba(0,0,0,.08); flex:1; }
        .card-resumo strong { font-size:28px; color:#003366; display:block; }
        /* Responsivo básico */
        @media (max-width:767px) {
            header { padding:12px 15px; }
            .logo-area img { width:48px; height:60px; }
            .logo { font-size:18px; }
            .menu-toggle { display:flex; }
            .menu { display:none; position:absolute; top:76px; left:15px; right:15px; width:calc(100% - 30px); grid-auto-flow:row; gap:4px; margin:0; padding:15px; background:rgba(0,51,102,.98); border-radius:15px; box-shadow:0 15px 35px rgba(0,0,0,.3); }
            .menu.active { display:grid; }
            .menu li { width:100%; padding:2px 0; }
            .menu a { width:100%; text-align:left; padding:13px 15px; }
            .slider { height:280px; }
            .slider-info { bottom:15px; font-size:12px; padding:8px 15px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:90%; }
            .noticia-item { flex-direction:column; text-align:center; padding:18px; }
            .noticia-imagem { width:100%; height:250px; }
            .texto { text-align:center; }
            .evento-info { grid-template-columns:1fr; }
            .page-banner h1 { font-size:2em; }
            .info-card { width:100%; }
            .footer-container { flex-direction:column; text-align:center; }
            .diretoria-grid { grid-template-columns:1fr 1fr; }
            .membro-card .avatar { width:70px; height:70px; font-size:2em; }
            .admin-tabs button { flex:1 1 100%; }
            .admin-item { flex-direction:column; align-items:stretch; }
        }
        @media (max-width:480px) {
            .diretoria-grid { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>

<!-- ====================================================
     HEADER
     ==================================================== -->
<header>
    <div class="logo-area" onclick="navegarPara('home')">
        <img src="src/imagens/Assga_foto.jpg" alt="Logo ASSGA">
        <div class="logo">ASSGA</div>
    </div>
    <button class="menu-toggle" id="menuToggle" onclick="toggleMenu()"><i id="menuIcon" class="fas fa-bars"></i></button>
    <nav>
        <ul class="menu" id="menu">
            <li><a onclick="navegarPara('home')"><i class="fas fa-house"></i> Home</a></li>
            <li><a onclick="navegarPara('historia')"><i class="fas fa-book-open"></i> História</a></li>
            <li><a onclick="navegarPara('esportiva')"><i class="fas fa-futbol"></i> Esportiva</a></li>
            <li><a onclick="navegarPara('eventos')"><i class="fas fa-calendar-days"></i> Eventos</a></li>
            <li><a onclick="navegarPara('estatuto')"><i class="fas fa-file-contract"></i> Estatuto</a></li>
            <li><a onclick="navegarPara('diretoria')"><i class="fas fa-users"></i> Diretoria</a></li>
        </ul>
    </nav>
</header>

<!-- ====================================================
     CONTEÚDO PÚBLICO
     ==================================================== -->
<div class="container">
    <section id="secao-home" class="secao ativa">
        <div class="slider">
            <img id="slide" src="src/imagens/foto1.jpg" alt="ASSGA">
            <div class="slider-info" id="sliderInfo"><i class="fas fa-futbol"></i> ASSGA - Associação Desportiva</div>
        </div>
        <h2><i class="fas fa-newspaper"></i> Notícias</h2>
        <div id="noticiasPublicas"></div>
    </section>

    <section id="secao-historia" class="secao">
        <div class="page-banner"><h1><i class="fas fa-book-open"></i> Nossa História</h1><p>Conheça a trajetória da ASSGA.</p></div>
        <div id="historiaPublica"></div>
    </section>

    <section id="secao-esportiva" class="secao">
        <div class="page-banner"><h1><i class="fas fa-futbol"></i> Atividades Esportivas</h1><p>Conheça as modalidades oferecidas.</p></div>
        <div class="card"><h3><i class="fas fa-futbol"></i> Futsal</h3><p>Campeonatos e treinos para todas as idades.</p></div>
        <div class="card"><h3><i class="fas fa-volleyball"></i> Vôlei</h3><p>Vôlei de quadra e praia, com torneios especiais.</p></div>
        <div class="card"><h3><i class="fas fa-running"></i> Corridas</h3><p>Eventos de corrida rústica e caminhadas.</p></div>
        <div class="card"><h3><i class="fas fa-dumbbell"></i> Treinamento Funcional</h3><p>Aulas para melhorar condição física.</p></div>
        <div class="card"><h3><i class="fas fa-child"></i> Esportes Infantis</h3><p>Atividades para crianças, com foco em desenvolvimento.</p></div>
    </section>

    <section id="secao-eventos" class="secao">
        <div class="page-banner"><h1><i class="fas fa-calendar-days"></i> Eventos ASSGA</h1><p>Confira a programação e faça sua inscrição.</p></div>
        <div id="eventosPublicos"></div>
    </section>

    <section id="secao-estatuto" class="secao">
        <div class="page-banner"><h1><i class="fas fa-file-contract"></i> Estatuto</h1><p>Documentos oficiais e normas.</p></div>
        <div id="estatutoPublico"></div>
    </section>

    <section id="secao-diretoria" class="secao">
        <div class="page-banner"><h1><i class="fas fa-users"></i> Nossa Diretoria</h1><p>Conheça a equipe da ASSGA.</p></div>
        <div id="diretoriaPublica" class="diretoria-grid"></div>
    </section>

    <section id="secao-pagamento" class="secao">
        <!-- (conteúdo da página de pagamento, omitido por brevidade) -->
        <p style="text-align:center;font-size:1.2em;color:#003366;">Página de pagamento em desenvolvimento.</p>
    </section>

    <section class="info">
        <h2><i class="fas fa-comments"></i> Fale Conosco</h2>
        <div class="info-container" id="contatosPublicos">
            <!-- carregado via JS -->
        </div>
    </section>
</div>

<!-- ====================================================
     RODAPÉ
     ==================================================== -->
<footer>
    <div class="footer-container">
        <div class="footer-copy" id="footerCopy">
            <i class="fas fa-copyright"></i> 2026 ASSGA - Todos os direitos reservados
            <br><i class="fas fa-building"></i> CNPJ: 57.242.499/0001-60
            <br><i class="fas fa-futbol"></i> Associação Desportiva
            <br><a href="admin.html" class="link-admin"><i class="fas fa-lock"></i> Área Restrita</a>
        </div>
        <div class="footer-redes">
            <a href="https://www.instagram.com/assga_2019/" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://www.youtube.com/@ASSGAESPORTES" target="_blank"><i class="fab fa-youtube"></i></a>
            <a href="https://wa.me/5584996981248" target="_blank"><i class="fab fa-whatsapp"></i></a>
            <a href="mailto:assgar2019@gmail.com"><i class="fas fa-envelope"></i></a>
        </div>
    </div>
</footer>

<!-- ====================================================
     ÁREA ADMINISTRATIVA
     ==================================================== -->
<div class="container" id="secao-admin" style="display:none;">
    <div id="adminLogin" class="login-box">
        <img src="src/imagens/Assga_foto.jpg" alt="ASSGA">
        <h1><i class="fas fa-lock"></i> Área Restrita</h1>
        <p>Digite a senha para acessar o painel.</p>
        <input type="password" id="senhaAdmin" placeholder="Senha">
        <button onclick="fazerLogin()">Entrar</button>
        <div class="erro-login" id="erroLogin">Senha incorreta.</div>
    </div>
    <div id="painelAdmin" style="display:none;">
        <div class="painel-header">
            <h1><i class="fas fa-user-shield"></i> Painel Administrativo</h1>
            <div class="admin-info">
                <span><i class="fas fa-user"></i> Administrador</span>
                <button class="btn-sair" onclick="sairAdmin()">Sair</button>
            </div>
        </div>
        <div id="adminMensagem" class="admin-mensagem"></div>
        <div class="admin-tabs">
            <button class="ativo" onclick="adminAba('noticias')"><i class="fas fa-newspaper"></i> Notícias</button>
            <button onclick="adminAba('eventos')"><i class="fas fa-calendar"></i> Eventos</button>
            <button onclick="adminAba('diretoria')"><i class="fas fa-users"></i> Diretoria</button>
            <button onclick="adminAba('estatuto')"><i class="fas fa-file-contract"></i> Estatuto</button>
            <button onclick="adminAba('inscricoes')"><i class="fas fa-list"></i> Inscrições</button>
            <button onclick="adminAba('config')"><i class="fas fa-cog"></i> Configurações</button>
        </div>
        <div class="admin-painel-conteudo" id="adminConteudo"></div>
    </div>
</div>

<!-- ====================================================
     JAVASCRIPT
     ==================================================== -->
<script>
// ============================================================
// NAVEGAÇÃO
// ============================================================
function navegarPara(secao) {
    document.querySelectorAll('.secao').forEach(el => el.classList.remove('ativa'));
    if (secao === 'admin') {
        document.getElementById('secao-admin').style.display = 'block';
        document.querySelectorAll('.secao').forEach(el => el.classList.remove('ativa'));
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (sessionStorage.getItem('assga_admin_logado') === 'true') {
            document.getElementById('adminLogin').style.display = 'none';
            document.getElementById('painelAdmin').style.display = 'block';
            adminAba('noticias');
        } else {
            document.getElementById('adminLogin').style.display = 'block';
            document.getElementById('painelAdmin').style.display = 'none';
        }
        return;
    }
    document.getElementById('secao-admin').style.display = 'none';
    const alvo = document.getElementById('secao-' + secao);
    if (alvo) alvo.classList.add('ativa');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function toggleMenu() {
    const menu = document.getElementById('menu');
    const icon = document.getElementById('menuIcon');
    menu.classList.toggle('active');
    icon.className = menu.classList.contains('active') ? 'fas fa-xmark' : 'fas fa-bars';
}

// ============================================================
// FUNÇÕES DE API
// ============================================================
async function api(acao, metodo = 'GET', dados = null) {
    const url = '?api=' + acao;
    const opts = { method: metodo, headers: { 'Content-Type': 'application/json' } };
    if (dados) opts.body = JSON.stringify(dados);
    const res = await fetch(url, opts);
    return res.json();
}

// ============================================================
// RENDERIZAÇÃO PÚBLICA
// ============================================================
async function carregarPublico() {
    // Notícias
    const noticias = await api('noticias');
    const containerN = document.getElementById('noticiasPublicas');
    if (noticias.length === 0) containerN.innerHTML = '<p style="text-align:center;color:#666;">Nenhuma notícia cadastrada.</p>';
    else {
        containerN.innerHTML = noticias.map(n => `
            <article class="noticia-item">
                <a class="noticia-imagem" href="${n.imagem || 'src/imagens/Assga_foto.jpg'}" target="_blank">
                    <img src="${n.imagem || 'src/imagens/Assga_foto.jpg'}" alt="${n.titulo}" onerror="this.src='src/imagens/Assga_foto.jpg'">
                </a>
                <div class="texto">
                    <h3><i class="fas fa-newspaper"></i> ${n.titulo}</h3>
                    <data><i class="fas fa-calendar-check"></i> ${n.data || ''}</data>
                    <p>${n.conteudo}</p>
                </div>
            </article>
        `).join('');
    }

    // Eventos
    const eventos = await api('eventos');
    const containerE = document.getElementById('eventosPublicos');
    if (eventos.length === 0) containerE.innerHTML = '<p style="text-align:center;color:#666;">Nenhum evento cadastrado.</p>';
    else {
        containerE.innerHTML = eventos.map(e => {
            const cls = e.status === 'aberto' ? 'status-aberto' : (e.status === 'andamento' ? 'status-andamento' : 'status-fechado');
            const lbl = e.status === 'aberto' ? 'Inscrições Abertas' : (e.status === 'andamento' ? 'Em andamento' : 'Inscrições Encerradas');
            return `
                <div class="card">
                    <span class="badge ${cls}"><i class="fas fa-circle-check"></i> ${lbl}</span>
                    <h3><i class="fas fa-calendar-alt"></i> ${e.titulo}</h3>
                    <div class="evento-grid-info">
                        <div class="info-item"><i class="fas fa-calendar-days"></i> ${e.data_inicio}${e.data_fim ? ' a '+e.data_fim : ''}</div>
                        <div class="info-item"><i class="fas fa-location-dot"></i> ${e.local}</div>
                        <div class="info-item"><i class="fas fa-ticket"></i> ${e.vagas} vagas</div>
                        <div class="info-item"><i class="fas fa-money-bill-wave"></i> R$ ${Number(e.valor).toFixed(2)}</div>
                    </div>
                    <p>${e.descricao}</p>
                    ${e.status === 'aberto' ? `<a onclick="navegarPara('pagamento')" class="botao-inscricao"><i class="fas fa-ticket"></i> Fazer inscrição</a>` : ''}
                </div>
            `;
        }).join('');
    }

    // Diretoria
    const diretoria = await api('diretoria');
    const containerD = document.getElementById('diretoriaPublica');
    if (diretoria.length === 0) containerD.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#666;">Nenhum membro cadastrado.</p>';
    else {
        containerD.innerHTML = diretoria.map(m => `
            <div class="membro-card">
                <div class="avatar"><i class="fas fa-user"></i></div>
                <h3>${m.nome}</h3>
                <span class="cargo"><i class="fas fa-star"></i> ${m.cargo}</span>
                <p>${m.descricao || ''}</p>
                <div class="contato">
                    ${m.email ? `<a href="mailto:${m.email}"><i class="fas fa-envelope"></i></a>` : ''}
                    ${m.telefone ? `<a href="tel:${m.telefone}"><i class="fas fa-phone"></i></a>` : ''}
                </div>
            </div>
        `).join('');
    }

    // Estatuto
    const estatuto = await api('estatuto');
    const containerS = document.getElementById('estatutoPublico');
    if (estatuto.length === 0) containerS.innerHTML = '<p style="text-align:center;color:#666;">Estatuto não cadastrado.</p>';
    else {
        containerS.innerHTML = estatuto.map(e => `<div class="card">${e.conteudo}</div>`).join('');
    }

    // Configurações
    const config = await api('config');
    if (config.length > 0) {
        const c = config[0];
        document.getElementById('footerCopy').innerHTML = `
            <i class="fas fa-copyright"></i> 2026 ${c.nome_associacao || 'ASSGA'} - Todos os direitos reservados
            <br><i class="fas fa-building"></i> CNPJ: ${c.cnpj || '57.242.499/0001-60'}
            <br><i class="fas fa-futbol"></i> ${c.nome_associacao || 'Associação Desportiva'}
            <br><a href="admin.html" class="link-admin"><i class="fas fa-lock"></i> Área Restrita</a>
        `;
        // Contatos
        document.getElementById('contatosPublicos').innerHTML = `
            <div class="info-card">
                <div class="icon"><i class="fas fa-location-dot"></i></div>
                <h3><i class="fas fa-map-marker-alt"></i> Endereço</h3>
                <p>${c.endereco || 'São Gonçalo do Amarante - RN'}</p>
            </div>
            <div class="info-card">
                <div class="icon"><i class="fas fa-envelope"></i></div>
                <h3><i class="fas fa-at"></i> Email</h3>
                <p>${c.email || 'assgar2019@gmail.com'}</p>
                <a href="mailto:${c.email || 'assgar2019@gmail.com'}"><i class="fas fa-paper-plane"></i></a>
            </div>
            <div class="info-card">
                <div class="icon"><i class="fas fa-phone"></i></div>
                <h3><i class="fas fa-phone"></i> Telefone</h3>
                <p>${c.telefone || '(84) 99698-1248'}</p>
                <a href="tel:${c.telefone || '+5584996981248'}"><i class="fas fa-phone-volume"></i></a>
            </div>
            <div class="info-card">
                <div class="icon"><i class="fas fa-building"></i></div>
                <h3><i class="fas fa-id-card"></i> CNPJ</h3>
                <p>${c.cnpj || '57.242.499/0001-60'}</p>
            </div>
        `;
    }
}

// ============================================================
// ADMIN - LOGIN
// ============================================================
async function fazerLogin() {
    const senha = document.getElementById('senhaAdmin').value;
    const res = await api('login', 'POST', { senha });
    if (res.status === 'ok') {
        sessionStorage.setItem('assga_admin_logado', 'true');
        document.getElementById('adminLogin').style.display = 'none';
        document.getElementById('painelAdmin').style.display = 'block';
        document.getElementById('erroLogin').style.display = 'none';
        adminAba('noticias');
    } else {
        document.getElementById('erroLogin').style.display = 'block';
    }
}

function sairAdmin() {
    sessionStorage.removeItem('assga_admin_logado');
    document.getElementById('adminLogin').style.display = 'block';
    document.getElementById('painelAdmin').style.display = 'none';
    navegarPara('home');
}

// ============================================================
// ADMIN - ABAS
// ============================================================
let abaAtual = 'noticias';

function adminAba(aba) {
    abaAtual = aba;
    document.querySelectorAll('.admin-tabs button').forEach(b => b.classList.remove('ativo'));
    document.querySelector(`.admin-tabs button[onclick*="${aba}"]`)?.classList.add('ativo');
    renderizarAdmin(aba);
}

function mostrarMensagemAdmin(texto, tipo) {
    const el = document.getElementById('adminMensagem');
    el.textContent = texto;
    el.className = 'admin-mensagem ' + tipo;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 4000);
}

// ============================================================
// RENDERIZAÇÃO DAS ABAS ADMIN
// ============================================================
async function renderizarAdmin(aba) {
    const container = document.getElementById('adminConteudo');
    switch (aba) {
        case 'noticias': container.innerHTML = await adminNoticias(); break;
        case 'eventos': container.innerHTML = await adminEventos(); break;
        case 'diretoria': container.innerHTML = await adminDiretoria(); break;
        case 'estatuto': container.innerHTML = await adminEstatuto(); break;
        case 'inscricoes': container.innerHTML = await adminInscricoes(); break;
        case 'config': container.innerHTML = await adminConfig(); break;
        default: container.innerHTML = '<p>Selecione uma aba.</p>';
    }
}

// ---------- NOTÍCIAS ----------
async function adminNoticias() {
    const noticias = await api('noticias');
    let html = `
        <h3><i class="fas fa-newspaper"></i> Gerenciar Notícias</h3>
        <form onsubmit="salvarNoticia(event)">
            <input type="hidden" id="noticiaId">
            <div class="form-group"><label>Título</label><input type="text" id="noticiaTitulo" required></div>
            <div class="form-group"><label>Conteúdo</label><textarea id="noticiaConteudo" required></textarea></div>
            <div class="form-group"><label>URL da Imagem</label><input type="text" id="noticiaImagem" placeholder="src/imagens/..."></div>
            <div class="form-group"><label>Data</label><input type="date" id="noticiaData" required></div>
            <button type="submit">Salvar</button>
            <button type="button" onclick="limparFormNoticia()" style="background:#ccc;color:#333;border:0;padding:12px 25px;border-radius:8px;cursor:pointer;">Cancelar</button>
        </form>
        <div class="admin-lista">
            ${noticias.length === 0 ? '<p>Nenhuma notícia.</p>' : noticias.map(n => `
                <div class="admin-item">
                    <div class="info"><strong>${n.titulo}</strong> - ${n.data || ''}<br><small>${n.conteudo.substring(0,80)}${n.conteudo.length>80?'...':''}</small></div>
                    <div class="acoes">
                        <button class="btn-editar" onclick="editarNoticia(${n.id})">Editar</button>
                        <button class="btn-excluir-admin" onclick="excluirNoticia(${n.id})">Excluir</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    return html;
}

window.salvarNoticia = async function(e) {
    e.preventDefault();
    const id = document.getElementById('noticiaId').value;
    const dados = {
        titulo: document.getElementById('noticiaTitulo').value,
        conteudo: document.getElementById('noticiaConteudo').value,
        imagem: document.getElementById('noticiaImagem').value || 'src/imagens/Assga_foto.jpg',
        data: document.getElementById('noticiaData').value.split('-').reverse().join('/')
    };
    if (id) dados.id = parseInt(id);
    await api('salvar_noticia', 'POST', dados);
    mostrarMensagemAdmin('Notícia salva!', 'sucesso');
    limparFormNoticia();
    renderizarAdmin('noticias');
    carregarPublico();
};

window.editarNoticia = function(id) {
    // Buscar a notícia via API e preencher
    fetch('?api=noticias').then(r=>r.json()).then(data => {
        const n = data.find(item => item.id === id);
        if (!n) return;
        document.getElementById('noticiaId').value = n.id;
        document.getElementById('noticiaTitulo').value = n.titulo;
        document.getElementById('noticiaConteudo').value = n.conteudo;
        document.getElementById('noticiaImagem').value = n.imagem || '';
        const parts = n.data.split('/');
        if (parts.length === 3) document.getElementById('noticiaData').value = `${parts[2]}-${parts[1]}-${parts[0]}`;
    });
};

window.excluirNoticia = async function(id) {
    if (!confirm('Excluir notícia?')) return;
    await api('excluir_noticia&id=' + id);
    mostrarMensagemAdmin('Notícia excluída.', 'sucesso');
    renderizarAdmin('noticias');
    carregarPublico();
};

window.limparFormNoticia = function() {
    ['noticiaId','noticiaTitulo','noticiaConteudo','noticiaImagem','noticiaData'].forEach(id => document.getElementById(id).value = '');
};

// ---------- EVENTOS ----------
async function adminEventos() {
    const eventos = await api('eventos');
    let html = `
        <h3><i class="fas fa-calendar"></i> Gerenciar Eventos</h3>
        <form onsubmit="salvarEvento(event)">
            <input type="hidden" id="eventoId">
            <div class="form-group"><label>Título</label><input type="text" id="eventoTitulo" required></div>
            <div class="form-group"><label>Descrição</label><textarea id="eventoDescricao" required></textarea></div>
            <div class="form-group"><label>Data Início</label><input type="date" id="eventoDataInicio" required></div>
            <div class="form-group"><label>Data Fim</label><input type="date" id="eventoDataFim"></div>
            <div class="form-group"><label>Local</label><input type="text" id="eventoLocal" required></div>
            <div class="form-group"><label>Vagas</label><input type="number" id="eventoVagas" required min="1"></div>
            <div class="form-group"><label>Valor (R$)</label><input type="number" id="eventoValor" required min="0" step="0.01"></div>
            <div class="form-group"><label>Status</label>
                <select id="eventoStatus">
                    <option value="aberto">Aberto</option>
                    <option value="andamento">Em andamento</option>
                    <option value="fechado">Fechado</option>
                </select>
            </div>
            <button type="submit">Salvar</button>
            <button type="button" onclick="limparFormEvento()" style="background:#ccc;color:#333;border:0;padding:12px 25px;border-radius:8px;cursor:pointer;">Cancelar</button>
        </form>
        <div class="admin-lista">
            ${eventos.length === 0 ? '<p>Nenhum evento.</p>' : eventos.map(e => `
                <div class="admin-item">
                    <div class="info"><strong>${e.titulo}</strong> - ${e.data_inicio} ${e.data_fim ? 'a '+e.data_fim : ''}<br><small>${e.descricao.substring(0,60)}${e.descricao.length>60?'...':''}</small></div>
                    <div class="acoes">
                        <button class="btn-editar" onclick="editarEvento(${e.id})">Editar</button>
                        <button class="btn-excluir-admin" onclick="excluirEvento(${e.id})">Excluir</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    return html;
}

window.salvarEvento = async function(e) {
    e.preventDefault();
    const id = document.getElementById('eventoId').value;
    const dados = {
        titulo: document.getElementById('eventoTitulo').value,
        descricao: document.getElementById('eventoDescricao').value,
        data_inicio: document.getElementById('eventoDataInicio').value.split('-').reverse().join('/'),
        data_fim: document.getElementById('eventoDataFim').value ? document.getElementById('eventoDataFim').value.split('-').reverse().join('/') : '',
        local: document.getElementById('eventoLocal').value,
        vagas: parseInt(document.getElementById('eventoVagas').value),
        valor: parseFloat(document.getElementById('eventoValor').value),
        status: document.getElementById('eventoStatus').value
    };
    if (id) dados.id = parseInt(id);
    await api('salvar_evento', 'POST', dados);
    mostrarMensagemAdmin('Evento salvo!', 'sucesso');
    limparFormEvento();
    renderizarAdmin('eventos');
    carregarPublico();
};

window.editarEvento = function(id) {
    fetch('?api=eventos').then(r=>r.json()).then(data => {
        const e = data.find(item => item.id === id);
        if (!e) return;
        document.getElementById('eventoId').value = e.id;
        document.getElementById('eventoTitulo').value = e.titulo;
        document.getElementById('eventoDescricao').value = e.descricao;
        const partsI = e.data_inicio.split('/');
        if (partsI.length === 3) document.getElementById('eventoDataInicio').value = `${partsI[2]}-${partsI[1]}-${partsI[0]}`;
        if (e.data_fim) {
            const partsF = e.data_fim.split('/');
            if (partsF.length === 3) document.getElementById('eventoDataFim').value = `${partsF[2]}-${partsF[1]}-${partsF[0]}`;
        }
        document.getElementById('eventoLocal').value = e.local;
        document.getElementById('eventoVagas').value = e.vagas;
        document.getElementById('eventoValor').value = e.valor;
        document.getElementById('eventoStatus').value = e.status;
    });
};

window.excluirEvento = async function(id) {
    if (!confirm('Excluir evento?')) return;
    await api('excluir_evento&id=' + id);
    mostrarMensagemAdmin('Evento excluído.', 'sucesso');
    renderizarAdmin('eventos');
    carregarPublico();
};

window.limparFormEvento = function() {
    ['eventoId','eventoTitulo','eventoDescricao','eventoDataInicio','eventoDataFim','eventoLocal','eventoVagas','eventoValor'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('eventoStatus').value = 'aberto';
};

// ---------- DIRETORIA ----------
async function adminDiretoria() {
    const membros = await api('diretoria');
    let html = `
        <h3><i class="fas fa-users"></i> Gerenciar Diretoria</h3>
        <form onsubmit="salvarMembro(event)">
            <input type="hidden" id="membroId">
            <div class="form-group"><label>Nome</label><input type="text" id="membroNome" required></div>
            <div class="form-group"><label>Cargo</label><input type="text" id="membroCargo" required></div>
            <div class="form-group"><label>Descrição</label><textarea id="membroDescricao"></textarea></div>
            <div class="form-group"><label>Email</label><input type="email" id="membroEmail"></div>
            <div class="form-group"><label>Telefone</label><input type="text" id="membroTelefone"></div>
            <button type="submit">Salvar</button>
            <button type="button" onclick="limparFormMembro()" style="background:#ccc;color:#333;border:0;padding:12px 25px;border-radius:8px;cursor:pointer;">Cancelar</button>
        </form>
        <div class="admin-lista">
            ${membros.length === 0 ? '<p>Nenhum membro.</p>' : membros.map(m => `
                <div class="admin-item">
                    <div class="info"><strong>${m.nome}</strong> - ${m.cargo}<br><small>${m.descricao || ''}</small></div>
                    <div class="acoes">
                        <button class="btn-editar" onclick="editarMembro(${m.id})">Editar</button>
                        <button class="btn-excluir-admin" onclick="excluirMembro(${m.id})">Excluir</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    return html;
}

window.salvarMembro = async function(e) {
    e.preventDefault();
    const id = document.getElementById('membroId').value;
    const dados = {
        nome: document.getElementById('membroNome').value,
        cargo: document.getElementById('membroCargo').value,
        descricao: document.getElementById('membroDescricao').value,
        email: document.getElementById('membroEmail').value,
        telefone: document.getElementById('membroTelefone').value
    };
    if (id) dados.id = parseInt(id);
    await api('salvar_membro', 'POST', dados);
    mostrarMensagemAdmin('Membro salvo!', 'sucesso');
    limparFormMembro();
    renderizarAdmin('diretoria');
    carregarPublico();
};

window.editarMembro = function(id) {
    fetch('?api=diretoria').then(r=>r.json()).then(data => {
        const m = data.find(item => item.id === id);
        if (!m) return;
        document.getElementById('membroId').value = m.id;
        document.getElementById('membroNome').value = m.nome;
        document.getElementById('membroCargo').value = m.cargo;
        document.getElementById('membroDescricao').value = m.descricao || '';
        document.getElementById('membroEmail').value = m.email || '';
        document.getElementById('membroTelefone').value = m.telefone || '';
    });
};

window.excluirMembro = async function(id) {
    if (!confirm('Excluir membro?')) return;
    await api('excluir_membro&id=' + id);
    mostrarMensagemAdmin('Membro excluído.', 'sucesso');
    renderizarAdmin('diretoria');
    carregarPublico();
};

window.limparFormMembro = function() {
    ['membroId','membroNome','membroCargo','membroDescricao','membroEmail','membroTelefone'].forEach(id => document.getElementById(id).value = '');
};

// ---------- ESTATUTO ----------
async function adminEstatuto() {
    const estatuto = await api('estatuto');
    const conteudo = estatuto.length > 0 ? estatuto[0].conteudo : '';
    let html = `
        <h3><i class="fas fa-file-contract"></i> Editar Estatuto</h3>
        <form onsubmit="salvarEstatuto(event)">
            <div class="form-group"><label>Conteúdo (HTML permitido)</label><textarea id="estatutoConteudo" rows="10">${conteudo}</textarea></div>
            <button type="submit">Salvar</button>
        </form>
    `;
    return html;
}

window.salvarEstatuto = async function(e) {
    e.preventDefault();
    const conteudo = document.getElementById('estatutoConteudo').value;
    let dados = { conteudo };
    const estatuto = await api('estatuto');
    if (estatuto.length > 0) dados.id = estatuto[0].id;
    await api('salvar_estatuto', 'POST', dados);
    mostrarMensagemAdmin('Estatuto salvo!', 'sucesso');
    renderizarAdmin('estatuto');
    carregarPublico();
};

// ---------- INSCRIÇÕES ----------
async function adminInscricoes() {
    const inscricoes = await api('inscricoes');
    const total = inscricoes.length;
    const pagos = inscricoes.filter(i => i.status_pagamento === 'PAGO').length;
    const naoPagos = total - pagos;
    let html = `
        <h3><i class="fas fa-list"></i> Gerenciar Inscrições</h3>
        <div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:20px;">
            <div class="card-resumo">Total: <strong>${total}</strong></div>
            <div class="card-resumo" style="background:#d9fbe5;">Pagos: <strong>${pagos}</strong></div>
            <div class="card-resumo" style="background:#ffe0e0;">Não Pagos: <strong>${naoPagos}</strong></div>
        </div>
        <div class="admin-lista">
            ${inscricoes.length === 0 ? '<p>Nenhuma inscrição.</p>' : inscricoes.map((ins, idx) => `
                <div class="admin-item">
                    <div class="info">
                        <strong>Vaga ${ins.vaga}/100</strong> - ${ins.nome}
                        <br><small>CPF: ${ins.cpf} | WhatsApp: ${ins.telefone} | Pagamento: ${ins.pagamento} | Status: ${ins.status_pagamento === 'PAGO' ? '🟢 PAGO' : '🔴 NÃO PAGO'}</small>
                        <br><small>Data: ${ins.data}</small>
                    </div>
                    <div class="acoes">
                        <button class="btn-pagamento ${ins.status_pagamento === 'PAGO' ? 'nao-pago' : ''}" onclick="alterarPagamento(${ins.id}, '${ins.status_pagamento === 'PAGO' ? 'NAO PAGO' : 'PAGO'}')">
                            ${ins.status_pagamento === 'PAGO' ? '🔴 Marcar não pago' : '🟢 Marcar pago'}
                        </button>
                        <button class="btn-excluir-admin" onclick="excluirInscricao(${ins.id})">Excluir</button>
                    </div>
                </div>
            `).join('')}
        </div>
        <button onclick="resetarInscricoes()" style="background:#d00000;color:#fff;border:0;padding:12px 25px;border-radius:8px;cursor:pointer;font-weight:bold;">Apagar todas</button>
    `;
    return html;
}

window.alterarPagamento = async function(id, status) {
    await api(`alterar_pagamento&id=${id}&status=${status}`);
    mostrarMensagemAdmin('Status alterado.', 'sucesso');
    renderizarAdmin('inscricoes');
};

window.excluirInscricao = async function(id) {
    if (!confirm('Excluir inscrição?')) return;
    await api('excluir_inscricao&id=' + id);
    mostrarMensagemAdmin('Inscrição excluída.', 'sucesso');
    renderizarAdmin('inscricoes');
};

window.resetarInscricoes = async function() {
    if (!confirm('Apagar TODAS as inscrições?')) return;
    await api('resetar_inscricoes');
    mostrarMensagemAdmin('Todas as inscrições foram apagadas.', 'sucesso');
    renderizarAdmin('inscricoes');
};

// ---------- CONFIG ----------
async function adminConfig() {
    const config = await api('config');
    const cfg = config.length > 0 ? config[0] : {};
    let html = `
        <h3><i class="fas fa-cog"></i> Configurações</h3>
        <form onsubmit="salvarConfig(event)">
            <div class="form-group"><label>Senha Admin</label><input type="text" id="configSenha" value="${cfg.senha || 'ASSGA2026'}" required></div>
            <div class="form-group"><label>Nome da Associação</label><input type="text" id="configNome" value="${cfg.nome_associacao || 'ASSGA - Associação Desportiva'}" required></div>
            <div class="form-group"><label>Endereço</label><input type="text" id="configEndereco" value="${cfg.endereco || 'São Gonçalo do Amarante - RN'}" required></div>
            <div class="form-group"><label>Email</label><input type="email" id="configEmail" value="${cfg.email || 'assgar2019@gmail.com'}" required></div>
            <div class="form-group"><label>Telefone</label><input type="text" id="configTelefone" value="${cfg.telefone || '(84) 99698-1248'}" required></div>
            <div class="form-group"><label>CNPJ</label><input type="text" id="configCnpj" value="${cfg.cnpj || '57.242.499/0001-60'}" required></div>
            <button type="submit">Salvar</button>
        </form>
    `;
    return html;
}

window.salvarConfig = async function(e) {
    e.preventDefault();
    const dados = {
        id: 1,
        senha: document.getElementById('configSenha').value,
        nome_associacao: document.getElementById('configNome').value,
        endereco: document.getElementById('configEndereco').value,
        email: document.getElementById('configEmail').value,
        telefone: document.getElementById('configTelefone').value,
        cnpj: document.getElementById('configCnpj').value
    };
    await api('salvar_config', 'POST', dados);
    mostrarMensagemAdmin('Configurações salvas!', 'sucesso');
    renderizarAdmin('config');
    carregarPublico();
};

// ============================================================
// INICIALIZAÇÃO
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    carregarPublico();
    if (window.location.hash === '#admin') {
        navegarPara('admin');
        return;
    }
    // Verifica se o admin já está logado
    if (sessionStorage.getItem('assga_admin_logado') === 'true' && document.getElementById('secao-admin').style.display !== 'none') {
        document.getElementById('adminLogin').style.display = 'none';
        document.getElementById('painelAdmin').style.display = 'block';
        adminAba('noticias');
    }
});
</script>

</body>
</html>