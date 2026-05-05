<?php
header('Content-Type: application/json; charset=utf-8');

$metodo = $_SERVER['REQUEST_METHOD'];
$arquivo = "tarefas.json";

switch ($metodo) {
    case 'GET':
        $id = $_GET['id'] ?? '';
        $achouresposta = false;

        $jsonrecebido = file_get_contents($arquivo);
        $lista = json_decode($jsonrecebido, true);


        foreach ($lista as $item) {
            if ($item["id"] == $id) {
                $achouresposta = true;
                $itemencontrado = $item;
                break;
            }
        }

        if ($achouresposta == true) {
            http_response_code(200);
            echo json_encode($itemencontrado);
            break;
        } else {
            $jsonrecebido = file_get_contents($arquivo);
            $dados = json_decode($jsonrecebido, true);


            http_response_code(200);
            echo json_encode($dados);
            break;
        }
    case 'POST':
        $jsonrecebido = file_get_contents('php://input');
        $novo = json_decode($jsonrecebido, true);

        $tarefa = $novo['tarefa'] ?? '';

        if ($tarefa == '') {
            http_response_code(400);
            echo json_encode(['erro' => "Tarefa não informada"]);
            exit;
        }

        if (!file_exists($arquivo)) {
            http_response_code(500);
            echo json_encode(['erro' => "Banco de dados não existe"]);
            exit;
        }

        $jsonrecebido = file_get_contents($arquivo);
        $lista = json_decode($jsonrecebido, true);

        $existe = false;

        foreach ($lista as $item) {
            if ($item['tarefa'] == $tarefa && $item['status'] == 'pendente') {
                $existe = true;
            }
        }

        if ($existe) {
            http_response_code(409);
            echo json_encode(['erro' => "Tarefa já existe"]);
            exit;
        }

        $lista[] = [
            'id' => rand(1000, 9999),
            'tarefa' => $tarefa,
            'status' => 'pendente'
        ];

        $jsonresposta = json_encode($lista, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($arquivo, $jsonresposta);

        http_response_code(201);
        echo json_encode(["sucesso" => "Tarefa criada"]);

        break;

    case 'PUT':
        $id = $_GET['id'] ?? null;
        $jsonrecebido = file_get_contents('php://input');
        $novoValor = json_decode($jsonrecebido, true);

        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID não informada"]);
            exit;
        }

        if (!file_exists($arquivo)) {
            http_response_code(500);
            echo json_encode(["erro" => "Banco de dados não existe"]);
            exit;
        }

        if (!isset($novoValor)) {
            http_response_code(400);
            echo json_encode(["erro" => "Novos valores não informados"]);
            exit;
        }

        $jsonrecebido = file_get_contents($arquivo);
        $lista = json_decode($jsonrecebido, true);

        $achouresposta = false;
        foreach ($lista as &$item) {
            if ($item['id'] == $id) {
                $achouresposta = true;

                if (isset($novoValor['tarefa'])) {
                    $item['tarefa'] = $novoValor['tarefa'];
                }
                if (isset($novoValor['status'])) {
                    $item['status'] = $novoValor['status'];
                }
                break;
            }
        }

        if ($achouresposta) {
            $jsonresposta = json_encode($lista, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            file_put_contents($arquivo, $jsonresposta);

            http_response_code(200);
            echo json_encode(["sucesso" => "Item editado"]);
        } else {
            http_response_code(404);
            echo json_encode(["erro" => "Id não encontrado"]);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID não informada"]);
            exit;
        }

        if (!file_exists($arquivo)) {
            http_response_code(500);
            echo json_encode(["erro" => "Banco de dados não existe"]);
            exit;
        }

        $jsonrecebido = file_get_contents($arquivo);
        $lista = json_decode($jsonrecebido, true);

        $achouresposta = false;
        foreach ($lista as $indice => $item) {
            if ($item['id'] == $id) {
                $achouresposta = true;
                unset($lista[$indice]);
                break;
            }
        }

        if ($achouresposta) {
            $lista = array_values($lista);
            $jsonresposta = json_encode($lista, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            file_put_contents($arquivo, $jsonresposta);

            http_response_code(200);
            echo json_encode(["sucesso" => "Item excluído"]);
        } else {
            http_response_code(404);
            echo json_encode(["erro" => "Item não encontrado"]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido']);
        break;
}
