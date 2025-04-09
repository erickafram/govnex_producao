<?php
header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentação da API de Consulta CNPJ/CPF</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        h1 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            margin-top: 30px;
            border-left: 4px solid #3498db;
            padding-left: 10px;
        }
        code {
            background-color: #f8f8f8;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: Consolas, Monaco, 'Andale Mono', monospace;
            font-size: 0.9em;
        }
        pre {
            background-color: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .endpoint {
            font-weight: bold;
            color: #2980b9;
        }
        .method {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            margin-right: 10px;
            font-weight: bold;
        }
        .get {
            background-color: #61affe;
            color: white;
        }
        .post {
            background-color: #49cc90;
            color: white;
        }
        .note {
            background-color: #ffffcc;
            padding: 15px;
            border-left: 5px solid #ffcc00;
            margin: 15px 0;
        }
        .important {
            background-color: #ffe6e6;
            padding: 15px;
            border-left: 5px solid #ff6666;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <h1>Documentação da API de Consulta CNPJ/CPF</h1>
    
    <h2>Visão Geral</h2>
    <p>
        A API de Consulta CNPJ/CPF permite a consulta de dados de pessoas jurídicas (CNPJ) e pessoas físicas (CPF) mediante autenticação
        e controle de créditos. Cada consulta realizada consome créditos da conta associada ao token ou domínio de origem.
    </p>
    
    <div class="note">
        <strong>Nota:</strong> Para utilizar esta API, você precisa de um token de acesso válido. Contate o administrador
        do sistema para solicitar um token para sua aplicação.
    </div>
    
    <h2>Autenticação</h2>
    <p>
        A autenticação é feita através de um token que deve ser enviado em todas as requisições. Você pode enviar o token de duas formas:
    </p>
    <ul>
        <li>Como parâmetro <code>token</code> na URL (método GET)</li>
        <li>Como parâmetro <code>token</code> no corpo da requisição (método POST)</li>
    </ul>

    <div class="important">
        <strong>Importante:</strong> Mantenha seu token seguro e não o compartilhe. Cada consulta realizada através
        do seu token consumirá créditos da sua conta.
    </div>
    
    <h2>Endpoints</h2>
    
    <h3>Consulta de CNPJ</h3>
    <div>
        <span class="method get">GET</span>
        <span class="endpoint">/api/proxy_api.php?token={seu_token}&cnpj={cnpj}</span>
    </div>
    <br>
    <p>Consulta dados de uma empresa pelo CNPJ.</p>
    
    <h4>Parâmetros</h4>
    <table>
        <tr>
            <th>Nome</th>
            <th>Tipo</th>
            <th>Obrigatório</th>
            <th>Descrição</th>
        </tr>
        <tr>
            <td>token</td>
            <td>string</td>
            <td>Sim</td>
            <td>Seu token de autenticação</td>
        </tr>
        <tr>
            <td>cnpj</td>
            <td>string</td>
            <td>Sim</td>
            <td>CNPJ a ser consultado (apenas números, 14 dígitos)</td>
        </tr>
        <tr>
            <td>domain</td>
            <td>string</td>
            <td>Não</td>
            <td>Domínio de origem da requisição (opcional, usado para vincular a consulta)</td>
        </tr>
    </table>
    
    <h4>Exemplo de Requisição</h4>
    <pre><code>GET /api/proxy_api.php?token=8ab984d986b155d84b4f88dec6d4f8c3cd2e11c685d9805107df78e94ab488ca&cnpj=12345678000199</code></pre>
    
    <h4>Exemplo de Resposta</h4>
    <pre><code>{
  "success": true,
  "data": {
    "cnpj": "12345678000199",
    "razao_social": "EMPRESA EXEMPLO LTDA",
    "nome_fantasia": "EXEMPLO",
    "situacao_cadastral": "ATIVA",
    "data_abertura": "2010-05-10",
    "endereco": {
      "logradouro": "RUA EXEMPLO",
      "numero": "123",
      "complemento": "SALA 101",
      "cep": "12345-678",
      "bairro": "CENTRO",
      "municipio": "SÃO PAULO",
      "uf": "SP"
    },
    "atividade_principal": {
      "codigo": "6202-3/00",
      "descricao": "DESENVOLVIMENTO DE PROGRAMAS DE COMPUTADOR SOB ENCOMENDA"
    }
  },
  "credito_restante": 45.26
}</code></pre>
    
    <h3>Consulta de CPF</h3>
    <div>
        <span class="method get">GET</span>
        <span class="endpoint">/api/proxy_api.php?token={seu_token}&cpf={cpf}</span>
    </div>
    <br>
    <p>Consulta dados de uma pessoa física pelo CPF.</p>
    
    <h4>Parâmetros</h4>
    <table>
        <tr>
            <th>Nome</th>
            <th>Tipo</th>
            <th>Obrigatório</th>
            <th>Descrição</th>
        </tr>
        <tr>
            <td>token</td>
            <td>string</td>
            <td>Sim</td>
            <td>Seu token de autenticação</td>
        </tr>
        <tr>
            <td>cpf</td>
            <td>string</td>
            <td>Sim</td>
            <td>CPF a ser consultado (apenas números, 11 dígitos)</td>
        </tr>
        <tr>
            <td>domain</td>
            <td>string</td>
            <td>Não</td>
            <td>Domínio de origem da requisição (opcional, usado para vincular a consulta)</td>
        </tr>
    </table>
    
    <h4>Exemplo de Requisição</h4>
    <pre><code>GET /api/proxy_api.php?token=8ab984d986b155d84b4f88dec6d4f8c3cd2e11c685d9805107df78e94ab488ca&cpf=12345678901</code></pre>
    
    <h4>Exemplo de Resposta</h4>
    <pre><code>{
  "success": true,
  "data": {
    "cpf": "123.456.789-01",
    "nome": "JOÃO DA SILVA",
    "data_nascimento": "1985-03-15",
    "situacao_cadastral": "REGULAR"
  },
  "credito_restante": 42.11
}</code></pre>
    
    <h2>Consulta via POST</h2>
    
    <p>Todos os endpoints também aceitam requisições POST, o que é recomendado para maior segurança do token.</p>
    
    <h4>Exemplo de Requisição POST</h4>
    <pre><code>POST /api/proxy_api.php
Content-Type: application/json

{
  "token": "8ab984d986b155d84b4f88dec6d4f8c3cd2e11c685d9805107df78e94ab488ca",
  "cnpj": "12345678000199",
  "domain": "meusite.com.br"
}</code></pre>
    
    <h2>Códigos de Status</h2>
    
    <table>
        <tr>
            <th>Código</th>
            <th>Descrição</th>
        </tr>
        <tr>
            <td>200</td>
            <td>Requisição bem-sucedida</td>
        </tr>
        <tr>
            <td>400</td>
            <td>Parâmetros inválidos ou faltando</td>
        </tr>
        <tr>
            <td>401</td>
            <td>Token não fornecido</td>
        </tr>
        <tr>
            <td>402</td>
            <td>Créditos insuficientes para realizar a consulta</td>
        </tr>
        <tr>
            <td>403</td>
            <td>Token inválido ou expirado</td>
        </tr>
        <tr>
            <td>500</td>
            <td>Erro interno do servidor</td>
        </tr>
    </table>
    
    <h2>Custos e Créditos</h2>
    
    <p>Cada consulta consome créditos da sua conta de acordo com a tabela abaixo:</p>
    
    <table>
        <tr>
            <th>Tipo de Consulta</th>
            <th>Custo (créditos)</th>
        </tr>
        <tr>
            <td>Consulta de CNPJ</td>
            <td>0.12</td>
        </tr>
        <tr>
            <td>Consulta de CPF</td>
            <td>0.15</td>
        </tr>
    </table>
    
    <p>Para verificar o seu saldo de créditos, contate o administrador do sistema.</p>
    
    <h2>Implementação no Cliente</h2>
    
    <h3>Exemplo em PHP</h3>
    <pre><code>// Exemplo de consulta em PHP
$cnpj = "12345678000199";
$token = "8ab984d986b155d84b4f88dec6d4f8c3cd2e11c685d9805107df78e94ab488ca";
$domain = "meusite.com.br";

// Configurar a requisição
$url = "https://govnex.site/govnex/api/proxy_api.php";

// Configurar os cabeçalhos para incluir o domínio de origem
$headers = [
    'Origin: https://' . $domain,
    'Referer: https://' . $domain . '/',
    'Host: govnex.site'
];

// Opção 1: Requisição via GET
$ch = curl_init($url . "?token=" . $token . "&cnpj=" . $cnpj . "&domain=" . $domain);

// Opção 2: Requisição via POST (mais segura)
/*
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'token' => $token,
    'cnpj' => $cnpj,
    'domain' => $domain
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
*/

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "Erro na requisição: " . curl_error($ch);
} else {
    $resultado = json_decode($response, true);
    
    if ($httpCode === 200 && isset($resultado['success']) && $resultado['success']) {
        // Sucesso na consulta
        echo "Dados encontrados: " . print_r($resultado['data'], true);
        echo "Crédito restante: " . $resultado['credito_restante'];
    } else {
        // Erro na consulta
        echo "Erro na consulta: " . ($resultado['error'] ?? "Código $httpCode");
    }
}

curl_close($ch);</code></pre>

    <h3>Exemplo em JavaScript</h3>
    <pre><code>// Exemplo de consulta em JavaScript
const consultarCNPJ = async (cnpj) => {
    const token = '8ab984d986b155d84b4f88dec6d4f8c3cd2e11c685d9805107df78e94ab488ca';
    const domain = window.location.hostname;
    
    try {
        // Opção 1: Via GET
        const url = `https://govnex.site/govnex/api/proxy_api.php?token=${token}&cnpj=${cnpj}&domain=${domain}`;
        
        // Opção 2: Via POST (mais segura)
        /*
        const url = 'https://govnex.site/govnex/api/proxy_api.php';
        const options = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Origin': window.location.origin,
                'X-Domain': domain
            },
            body: JSON.stringify({
                token: token,
                cnpj: cnpj,
                domain: domain
            })
        };
        */
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (response.ok && data.success) {
            console.log('Dados encontrados:', data.data);
            console.log('Crédito restante:', data.credito_restante);
            return data;
        } else {
            console.error('Erro na consulta:', data.error || response.statusText);
            throw new Error(data.error || 'Erro na consulta');
        }
    } catch (error) {
        console.error('Erro ao realizar consulta:', error);
        throw error;
    }
};</code></pre>

    <h2>Suporte</h2>
    <p>
        Em caso de dúvidas ou problemas com a API, entre em contato com nossa equipe de suporte através do e-mail
        <a href="mailto:suporte@govnex.site">suporte@govnex.site</a>.
    </p>
    
    <footer style="margin-top: 50px; border-top: 1px solid #ddd; padding-top: 20px; text-align: center; color: #777;">
        <p>© <?php echo date('Y'); ?> GovNEX - Todos os direitos reservados</p>
    </footer>
</body>
</html> 