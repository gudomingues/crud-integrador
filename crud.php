<?php
/**
 *  CRUD Integrador
 *
 * @category    CRUD
 * @package     crud
 */

// Dados de conexão com o banco de dados
$host = "localhost";
$user = "gustavo";
$pass = "Domingues@2024";
$db = "crud_int";

// Captura a ação que deve ser executada
$action = $_REQUEST["action"] ?? null;

// Identifica a ação e invoca o método correspondente
switch ($action) {
    case "lista":
        carregarLista();
        break;
    case "salvar":
        salvarForm();
        break;
    case "excluir":
        excluirForm();
        break;
    case "buscar":
        carregarCliente();
        break;
}

// Método que carrega a lista de clientes cadastrados
function carregarLista() {
    global $host, $user, $pass, $db;

    $mysqli = new mysqli($host, $user, $pass, $db);
    if ($mysqli->connect_errno) {
        die("Erro ao conectar ao banco: " . $mysqli->connect_error);
    }

    $sql = "SELECT * FROM cliente ORDER BY id DESC";
    $res = $mysqli->query($sql);
    if (!$res) {
        die("Erro ao executar SQL: " . $mysqli->error);
    }

    if ($res->num_rows === 0) {
        echo "Nenhum cadastro realizado até o momento.";
        return;
    }

    // Monta tabela de resultados na página
    $output = "<table>";
    while ($d = $res->fetch_assoc()) {
        $output .= "<tr>
                        <td style='width:25%'><img class='thumb' src='/crud/imagens/{$d['foto']}' /></td>
                        <td>
                            <p class='plus'>{$d['nome']}</p>
                            <p>{$d['email']}</p>
                            <p>{$d['telefone']}</p>
                        </td>
                        <td style='width:25%'><input type='button' class='button' value='Editar' onClick='carregarCliente(\"{$d['id']}\");'></td>
                        <td style='width:10%'><input type='button' class='button delete' value='X' onClick='excluirRegistro(\"{$d['id']}\");'></td>
                    </tr>";
    }
    $output .= "</table>";

    echo $output;
    $res->close();
    $mysqli->close();
}

// Método que carrega dados do cliente selecionado para alteração
function carregarCliente() {
    if (empty($_POST) || !isset($_POST["id"]) || !is_numeric($_POST["id"])) {
        echo "Dados do formulário não chegaram ou ID inválido.";
        return;
    }

    $id = (int)$_POST["id"];
    global $host, $user, $pass, $db;

    $mysqli = new mysqli($host, $user, $pass, $db);
    if ($mysqli->connect_errno) {
        die("Erro ao conectar ao banco: " . $mysqli->connect_error);
    }

    $stmt = $mysqli->prepare("SELECT * FROM cliente WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    } else {
        echo "Cliente não encontrado.";
    }

    $mysqli->close();
}

// Método que salva ou atualiza o formulário de cadastro do cliente
function salvarForm() {
    if (empty($_POST)) {
        echo "Dados do formulário não chegaram.";
        return;
    }

    $id = (int)$_POST["id"];
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $telefone = $_POST["telefone"];
    $foto = $_FILES['foto'] ?? null;

    $validationError = validarForm($id, $nome, $email, $telefone, $foto);
    if ($validationError) {
        echo "Problema encontrado:<br>" . $validationError;
        return;
    }

    $nomeImagem = handleFileUpload($foto);

    global $host, $user, $pass, $db;
    $mysqli = new mysqli($host, $user, $pass, $db);
    if ($mysqli->connect_errno) {
        die("Erro ao conectar ao banco: " . $mysqli->connect_error);
    }

    $sql = $id > 0 ? 
        "UPDATE cliente SET nome=?, email=?, telefone=?, foto=? WHERE id=?" : 
        "INSERT INTO cliente (nome, email, telefone, foto) VALUES (?, ?, ?, ?)";
        
    $stmt = $mysqli->prepare($sql);
    if ($id > 0) {
        $stmt->bind_param('ssisi', $nome, $email, $telefone, $nomeImagem, $id);
    } else {
        $stmt->bind_param('ssis', $nome, $email, $telefone, $nomeImagem);
    }

    if ($stmt->execute()) {
        echo $id > 0 ? "Cliente atualizado com sucesso!" : "Cliente cadastrado com sucesso!";
    } else {
        echo "Erro ao salvar dados: " . $stmt->error;
    }

    $stmt->close();
    $mysqli->close();
}

// Método que exclui registro do cliente
function excluirForm() {
    if (empty($_POST) || !isset($_POST["id"]) || !is_numeric($_POST["id"])) {
        echo "Dados do formulário não chegaram ou ID inválido.";
        return;
    }

    $id = (int)$_POST["id"];
    global $host, $user, $pass, $db;

    $mysqli = new mysqli($host, $user, $pass, $db);
    if ($mysqli->connect_errno) {
        die("Erro ao conectar ao banco: " . $mysqli->connect_error);
    }

    $stmt = $mysqli->prepare("DELETE FROM cliente WHERE id=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "Registro deletado com sucesso!";
    } else {
        echo "Erro ao deletar registro: " . $stmt->error;
    }

    $stmt->close();
    $mysqli->close();
}

// Método que valida os dados do formulário
function validarForm($id, $nome, $email, $telefone, $foto) {
    if (empty(trim($nome))) {
        return "Campo Nome deve ser preenchido.";
    }
    if (empty(trim($email))) {
        return "Campo Email deve ser preenchido.";
    }
    if (empty(trim($telefone))) {
        return "Campo Telefone deve ser preenchido.";
    }
    return null;
}

// Método que manipula o upload da imagem
function handleFileUpload($foto) {
    if (empty($foto) || $foto['error'] !== UPLOAD_ERR_OK) {
        return null; // Ou retorne um valor padrão se necessário
    }

    $nomeImagem = basename($foto['name']);
    $diretorio = $_SERVER['DOCUMENT_ROOT'] . '/crud/imagens/';
    if (!move_uploaded_file($foto['tmp_name'], $diretorio . $nomeImagem)) {
        die('Erro ao enviar arquivo de imagem.');
    }

    return $nomeImagem;
}
