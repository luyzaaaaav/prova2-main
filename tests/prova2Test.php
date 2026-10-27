
<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class UsuariosTest extends TestCase
{
    // Cada teste recebe uma aplicação nova
    private function criarApp()
    {
        return require __DIR__ . '/../src/app.php';
    }

    // Cria uma requisição JSON
    private function criarRequisicaoJson($metodo, $url, $dados = null)
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest($metodo, $url)
            ->withHeader('Content-Type', 'application/json');

        if ($dados !== null) {
            $request->getBody()->write(json_encode($dados));
            $request->getBody()->rewind();
        }

        return $request;
    }

    public function testGetStatus(): void
    {
        $app = $this->criarApp();

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/status');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('ok', $corpo['status']);
    }

    public function testGetUsuarios(): void
    {
        $app = $this->criarApp();

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/usuarios');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertCount(5, $corpo);
        $this->assertEquals('Victor', $corpo[0]['nome']);
    }

    public function testGetUsuariosFiltrandoPorNome(): void
    {
        $app = $this->criarApp();

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/usuarios?nome=luyza');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $corpo);
        $this->assertEquals('Luyza', $corpo[0]['nome']);
    }

    public function testGetUsuariosFiltrandoPorEmail(): void
    {
        $app = $this->criarApp();

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/usuarios?email=davi@gmail.com');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $corpo);
        $this->assertEquals('Davi', $corpo[0]['nome']);
    }

    public function testGetUsuarioPorId(): void
    {
        $app = $this->criarApp();

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/usuario/3');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Luyza', $corpo['nome']);
    }

    public function testGetUsuarioInexistente(): void
    {
        $app = $this->criarApp();

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/usuario/999');

        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Usuário nao encontrado', $corpo['erro']);
    }

    public function testPostCriaUsuario(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequisicaoJson(
            'POST',
            '/usuarios',
            [
                'nome' => 'Novo Usuário',
                'email' => 'novo@gmail.com',
                'login' => 'novo123',
                'senha' => 'abcd'
            ]
        );

        $response = $app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(6, $corpo['id']);
        $this->assertEquals('Novo Usuário', $corpo['nome']);
    }

    public function testPostComIdInformado(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequisicaoJson(
            'POST',
            '/usuarios',
            [
                'id' => 10,
                'nome' => 'Novo Usuário',
                'email' => 'novo@gmail.com',
                'login' => 'novo123',
                'senha' => 'abcd'
            ]
        );

        $response = $app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'O id não pode estar preenchido.',
            $corpo['erro']
        );
    }

    public function testPostComCamposFaltando(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequisicaoJson(
            'POST',
            '/usuarios',
            ['nome' => 'Novo Usuário']
        );

        $response = $app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'Todos os campos são obrigatórios.',
            $corpo['erro']
        );
    }

    public function testPutAtualizaUsuario(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequisicaoJson(
            'PUT',
            '/usuarios/1',
            [
                'nome' => 'Victor Atualizado',
                'email' => 'victor.novo@gmail.com',
                'login' => 'victornovo',
                'senha' => '4321'
            ]
        );

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Victor Atualizado', $corpo['nome']);
        $this->assertEquals('victor.novo@gmail.com', $corpo['email']);
    }

    public function testPutComCamposFaltando(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequisicaoJson(
            'PUT',
            '/usuarios/1',
            ['nome' => 'Victor Atualizado']
        );

        $response = $app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'Complete todos os campos.',
            $corpo['erro']
        );
    }

    public function testPutUsuarioInexistente(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequisicaoJson(
            'PUT',
            '/usuarios/999',
            [
                'nome' => 'Teste',
                'email' => 'teste@gmail.com',
                'login' => 'teste',
                'senha' => '1234'
            ]
        );

        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Usuário nao encontrado', $corpo['erro']);
    }

    public function testDeleteUsuario(): void
    {
        $app = $this->criarApp();

        $request = (new ServerRequestFactory())
            ->createServerRequest('DELETE', '/usuarios/1');

        $response = $app->handle($request);

        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDeleteUsuarioInexistente(): void
    {
        $app = $this->criarApp();

        $request = (new ServerRequestFactory())
            ->createServerRequest('DELETE', '/usuarios/999');

        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Usuário nao encontrado', $corpo['erro']);
    }
}
