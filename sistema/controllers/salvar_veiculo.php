<?php
// Controlador para salvar um veículo.
session_start();// Inicializa uma sessão
require_once __DIR__ . '/../init.php'; // Inicializa o ambiente e a conexão com o banco de dados.
// Verifica se o CSRF token está presente e é válido.
// Captura o token enviado via header
$headers = getallheaders();
$csrfTokenHeader = $headers['X-CSRF-Token'] ?? '';

if (!$csrfTokenHeader || $csrfTokenHeader !== $_SESSION['csrf_token']) {
    echo json_encode(["success" => false, "message" => "CSRF token inválido."]);
    exit;
}

// Verifica se  erros de conexão com o banco de dados
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
// header("Content-Type: application/json");

$idUsuario = $_SESSION['idusuarios'] ?? 0;
if ($idUsuario === 0) {
    echo json_encode(["success" => false, "message" => "Usuário não autenticado."]);
    exit;
}
// decodifica os dados recebidos via JSON.
$data = json_decode(file_get_contents("php://input"), true);
// busca os dados do veículo.
$tipo = trim($data['tipo'] ?? '');
$marca = trim($data['marca'] ?? '');
$modelo = trim($data['modelo'] ?? '');
$placa = strtoupper(trim($data['placa'] ?? ''));
// Verifica se os campos obrigatórios estão preenchidos.
if (empty($tipo) || empty($marca) || empty($modelo) || empty($placa)) {
    echo json_encode(["success" => false, "message" => "Todos os campos são obrigatórios."]);
    exit;
}

// Adicionando valor fixo para 'ativo'
$ativo = 1;
// insere os dados do veículo no banco de dados.
$stmt = $conn->prepare("INSERT INTO veiculos (tipo, marca, modelo, placa, usuarios_idusuarios, ativo) VALUES (?, ?, ?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("ssssii", $tipo, $marca, $modelo, $placa, $idUsuario, $ativo);
    // Executa a query e verifica se foi bem-sucedida.
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Veículo salvo com sucesso."]);
    } else {
        echo json_encode(["success" => false, "message" => "Erro ao salvar: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Erro na query: " . $conn->error]);
}

$conn->close();
?>