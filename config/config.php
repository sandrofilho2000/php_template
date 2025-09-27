<?php
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
//limite de upload
ini_set('upload_max_filesize', '50M');
//limite de tempo de execucao
//ini_set('max_execution_time', '300');

setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)) . DS);
define('CLASSES', ROOT . 'lib' . DS);
define('MINUTOS_SESSAO', 40);
define('USUARIO_SESSAO', 'tokenUser');
define('ARQUIVO_LOG', ROOT . 'log' . DS . 'errors.log');
define('QUERY_LOG', ROOT . 'log' . DS . 'querys.log');
define('TITULO_PAGINAS', 'Escola Exemplo');
define('TITULO_RELATORIO', 'Relatório Exemplo');
define('SUB_TITULO_RELATORIO', 'Subtítulo Exemplo');

#dados UNIDADE MOCK
define('UNL_NOME', 'Unidade MOCK 1');
define('UNL_CNPJ', '00.000.000/0000-00');
define('UNL_INCRICAO_MUNICIPAL', '000000');
define('UNL_LOGRADOURO_1', 'Rua Exemplo, 123');
define('UNL_CEP_LOGRADOURO_1', '00000-000');
define('UNL_LOGRADOURO_2', 'Avenida Teste, 456');
define('UNL_CEP_LOGRADOURO_2', '11111-111');
define('UNL_BAIRRO', 'Bairro Exemplo');
define('UNL_CIDADE', 'Cidade Exemplo, XX');
define('UNL_BAIRRO_CIDADE', 'Bairro Exemplo, Cidade Exemplo, XX');
define('UNL_TELEFONE', '0000-0000');
define('UNL_FAX', '1111-1111');

#dados UNIDADE MOCK 2
define('UICSA_NOME', 'Unidade MOCK 2');
define('UICSA_CNPJ', '11.111.111/1111-11');
define('UICSA_INCRICAO_MUNICIPAL', '1111111');
define('UICSA_LOGRADOURO', 'Av. Exemplo, 9999, sala 100');
define('UICSA_LOGRADOURO_SEMACENTO', 'Av. Exemplo, 9999, sala 100');
define('UICSA_CEP', '22222-222');
define('UICSA_BAIRRO', 'Bairro Teste');
define('UICSA_CIDADE', 'Cidade Mock');
define('UICSA_UF', 'XX');
define('UICSA_BAIRRO_CIDADE', 'Bairro Teste, Cidade Mock, XX');
define('UICSA_TELEFONE', '2222-2222');
define('UICSA_FAX', '3333-3333');

define('EMAIL', 'contato@mock.com');
define('HOMEPAGE', 'https://www.mock.com');
define('LOGOEMPRESA', ROOT . 'img' . DS . 'logo.png');

//ASSINADOR CONTRATOS DOCUSIGN MOCK
define('DOCUSIGN_CONTRATADO_EMAIL', 'assinatura@mock.com');
define('DOCUSIGN_CONTRATADO_NAME', 'Usuário Mock');

#ambiente
define('AMBIENTE', 'DESENV');

ini_set('ignore_repeated_errors', TRUE);
ini_set('display_errors', (AMBIENTE <> 'PRODUCAO'));
ini_set('log_errors', TRUE);
ini_set('error_log', ARQUIVO_LOG);

#TOKEN DE SEGURANCA
define('TOKEN', 'mockToken');

#DADOS DO CAPTCHA MOCK
define('USAR_CAPTCHA', (AMBIENTE == 'PRODUCAO'));
define('CAPTCHA_SITE_KEY', 'MOCK_SITE_KEY');
define('CAPTCHA_SECRET_KEY', 'MOCK_SECRET_KEY');
define('CAPTCHA_SITEVERIFY', 'https://www.google.com/recaptcha/api/siteverify');

#CREDENCIAIS DO EMAIL MOCK
define('EMAIL_FROM', 'Escola Exemplo');
define('EMAIL_HOST', 'smtp.mock.com');
define('EMAIL_USER', 'usuario@mock.com');
define('EMAIL_PASS', 'senha_mock_123');
define('EMAIL_PORT', 587);
define('EMAIL_SECURE', 'tls');

#chaves utilizadas nas funcoes secured_encrypt e secured_decrypt MOCK
define('FIRSTKEY', 'MOCK_FIRST_KEY_BASE64');
define('SECONDKEY', 'MOCK_SECOND_KEY_BASE64');

session_start();
require_once ROOT . 'config/funcoes.php';
require_once ROOT . 'config/credentials.php';
