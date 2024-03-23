<?php
namespace App;
require "../vendor/autoload.php";
use App\Model\Cliente;
use App\Repository\ClienteRepository;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

/*if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}*/

switch ($_SERVER['REQUEST_METHOD']) {
    case 'OPTIONS':
        $allowed_methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
        http_response_code(200);
        echo json_encode($allowed_methods);
        break;

    case 'POST':
        $requiredFields = ['nome', 'email', 'cidade', 'estado'];
        $data = json_decode(file_get_contents("php://input"));

        if (!isValid($data, $requiredFields)) {
            http_response_code(400);
            echo json_encode(["error" => "Dados de entrada inválidos."]);
            break;
        }

        $cliente = new Cliente();
        
        $cliente->setNome($data->nome);
        $cliente->setEmail($data->email);
        $cliente->setCidade($data->cidade);
        $cliente->setEstado($data->estado);


        $repository = new ClienteRepository();
        $success = $repository->insertCliente($cliente);
        if ($success) {
            http_response_code(200);
            echo json_encode(["message" => "Dados inseridos com sucesso."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Falha ao inserir dados."]);
        }
        break;

    case 'GET':
        $cliente = new Cliente();
        $repository = new ClienteRepository();

        if (isset($_GET['id'])) {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

                if ($id === false) {
                    http_response_code(400); 
                    echo json_encode(['error' => 'O valor do ID fornecido não é um inteiro válido.']);
                    exit;
                } else {
                    $cliente = new Cliente();
                    $repository = new ClienteRepository();
                    $cliente->setClienteId($id);
                    $result = $repository->getById($cliente);
                }
            } else {
                $result = $repository->getAll();
        }

        if ($result) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Nenhum dado encontrado."]);
        }
        break;
        
        case 'PUT':
            $data = json_decode(file_get_contents("php://input"));
            
            $cliente = new Cliente();
            $cliente_id = filter_input(INPUT_GET, 'cliente_id', FILTER_VALIDATE_INT);
            
            if ($cliente_id === false){
                http_response_code(400);
                echo json_encode(['error' => 'O valor do ID fornecido não é um inteiro válido.']);
                exit;
            }else{
                $cliente->setNome($data->nome);
                $cliente->setClienteId($data->cliente_id);
                $cliente->setEmail($data->email);
                $cliente->setCidade($data->cidade);
                $cliente->setEstado($data->estado);
            }
        
            $repository = new ClienteRepository();
            if ($repository->updateCliente($cliente)){
                http_response_code(200);
                echo json_encode(["message" => "Dados alterados com sucesso."]);
            }else{
                http_response_code(404);
                echo json_encode(["message" => "Cliente não encontrado."]);
            }
            break;
    
            case 'DELETE':
                $data = json_decode(file_get_contents("php://input")); //obtem dados
                $requiredFields  = ['id'];
        
                if (!isValid($data, $requiredFields)) {
                    http_response_code(400);
                    echo json_encode(["error" => "Dados de entrada inválidos."]);
                    break;
                }
        
                $id = $data->id;
                $cliente = new Cliente();
                $cliente->setClienteId($id);
        
                $repository = new ClienteRepository();
                $result = $repository->getById($cliente);
                echo json_encode($result);
        
                if(!$result){
                    http_response_code(404); 
                    echo json_encode(["message" => "Nenhum dado encontrado."]);
                }
                
                $success = $repository->deleteCliente($cliente);
        
                if ($success && $result) {
                    http_response_code(200); 
                    echo json_encode(["message" => "Dados apagados com sucesso."]);
                } else {
                    http_response_code(500); 
                    echo json_encode(["message" => "Falha ao apagar dados."]);
                }
                break;
        

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método não permitido."]);
        break;
}

function isValid($data, $requiredFields) {
    foreach ($requiredFields as $field) {
        if (!isset($data->$field) || empty($data->$field)) {
            return false;
        }
    }
    return true;
}
