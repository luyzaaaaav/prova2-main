
<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// Array inicial de usuários
$usuarios = [
    ["id" => 1, "nome" => "Victor", "email" => "victor@gmail.com", "login" => "chromekx", "senha" => "1234"],
    ["id" => 2, "nome" => "Davi", "email" => "davi@gmail.com", "login" => "PeopleEater", "senha" => "5678"],
    ["id" => 3, "nome" => "Luyza", "email" => "luyza@gmail.com", "login" => "betedabliu", "senha" => "8901"],
    ["id" => 4, "nome" => "Felipe", "email" => "felipe@gmail.com", "login" => "Parasintetico", "senha" => "2345"],
    ["id" => 5, "nome" => "Carlos", "email" => "carlos@gmail.com", "login" => "cazinn67", "senha" => "6789"]
];

// GET /status
$app->get('/status', function ($request, $response) {
    $response->getBody()->write(
        json_encode(['status' => 'ok'])
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

// GET /usuarios
// Permite listar todos ou filtrar por nome, email, login ou senha.
$app->get('/usuarios', function ($request, $response) use (&$usuarios) {
    $queryParams = $request->getQueryParams();

    $campos = ['nome', 'email', 'login', 'senha'];
    $filtrados = $usuarios;

    foreach ($campos as $campo) {
        if (isset($queryParams[$campo]) && $queryParams[$campo] !== '') {
            $valor = mb_strtolower((string) $queryParams[$campo]);

            $filtrados = array_filter(
                $usuarios,
                fn($usuario) => str_contains(
                    mb_strtolower((string) $usuario[$campo]),
                    $valor
                )
            );

            break;
        }
    }

    $response->getBody()->write(
        json_encode(array_values($filtrados), JSON_UNESCAPED_UNICODE)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

// GET /usuario/{id}
$app->get('/usuario/{id}', function ($request, $response, $args) use (&$usuarios) {
    $id = (int) $args['id'];

    foreach ($usuarios as $usuario) {
        if ($usuario['id'] === $id) {
            $response->getBody()->write(
                json_encode($usuario, JSON_UNESCAPED_UNICODE)
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
    }

    $response->getBody()->write(
        json_encode(['erro' => 'Usuário nao encontrado'], JSON_UNESCAPED_UNICODE)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
});

// POST /usuarios
$app->post('/usuarios', function ($request, $response) use (&$usuarios) {
    $dados = $request->getParsedBody();

    if (!is_array($dados)) {
        $dados = [];
    }

    // O ID é gerado automaticamente
    if (isset($dados['id'])) {
        $response->getBody()->write(
            json_encode(['erro' => 'O id não pode estar preenchido.'], JSON_UNESCAPED_UNICODE)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    // Verifica os campos obrigatórios
    foreach (['nome', 'email', 'login', 'senha'] as $campo) {
        if (
            !isset($dados[$campo]) ||
            !is_string($dados[$campo]) ||
            trim($dados[$campo]) === ''
        ) {
            $response->getBody()->write(
                json_encode(
                    ['erro' => 'Todos os campos são obrigatórios.'],
                    JSON_UNESCAPED_UNICODE
                )
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }

    $novoId = empty($usuarios)
        ? 1
        : max(array_column($usuarios, 'id')) + 1;

    $novoUsuario = [
        'id' => $novoId,
        'nome' => $dados['nome'],
        'email' => $dados['email'],
        'login' => $dados['login'],
        'senha' => $dados['senha']
    ];

    $usuarios[] = $novoUsuario;

    $response->getBody()->write(
        json_encode($novoUsuario, JSON_UNESCAPED_UNICODE)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

// PUT /usuarios/{id}
$app->put('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    $id = (int) $args['id'];
    $dados = $request->getParsedBody();

    foreach ($usuarios as &$usuario) {
        if ($usuario['id'] === $id) {
            if (!is_array($dados)) {
                $dados = [];
            }

            // PUT exige todos os campos
            foreach (['nome', 'email', 'login', 'senha'] as $campo) {
                if (
                    !isset($dados[$campo]) ||
                    !is_string($dados[$campo]) ||
                    trim($dados[$campo]) === ''
                ) {
                    unset($usuario);

                    $response->getBody()->write(
                        json_encode(
                            ['erro' => 'Complete todos os campos.'],
                            JSON_UNESCAPED_UNICODE
                        )
                    );

                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(400);
                }
            }

            $usuario['nome'] = $dados['nome'];
            $usuario['email'] = $dados['email'];
            $usuario['login'] = $dados['login'];
            $usuario['senha'] = $dados['senha'];

            $usuarioAtualizado = $usuario;
            unset($usuario);

            $response->getBody()->write(
                json_encode($usuarioAtualizado, JSON_UNESCAPED_UNICODE)
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
    }

    unset($usuario);

    $response->getBody()->write(
        json_encode(['erro' => 'Usuário nao encontrado'], JSON_UNESCAPED_UNICODE)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
});

// DELETE /usuarios/{id}
$app->delete('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    $id = (int) $args['id'];

    foreach ($usuarios as $indice => $usuario) {
        if ($usuario['id'] === $id) {
            array_splice($usuarios, $indice, 1);

            return $response->withStatus(204);
        }
    }

    $response->getBody()->write(
        json_encode(['erro' => 'Usuário nao encontrado'], JSON_UNESCAPED_UNICODE)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
});

// Retorna a aplicação para o PHPUnit.
// Não colocar $app->run() aqui!
return $app;
