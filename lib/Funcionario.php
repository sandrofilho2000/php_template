<?php

class Funcionario extends ClasseBase
{

    public $codigo;
    public $matricula;
    public $nome;
    public $funcao;
    public $endereco;
    public $bairro;
    public $cep;
    public $telefone;
    public $estadoCivil;
    public $conjuge;
    public $cartprof;
    public $cpf;
    public $cidade;
    public $naturalidade;
    public $nascimento;
    public $admissao;
    public $turno;
    public $sexo;
    public $celular;
    public $codhora;
    public $dtsaida;
    public $num_ident;
    public $uf_identidade;
    public $compl_ident;
    public $dt_ident;
    public $placa_carro;
    public $carro;
    public $email;
    public $pis;
    public $emailColegio;
    public $emailColegio2;
    public $tx_image;
    public $tx_img_delete;
    public $contatoEmergenciaNome;
    public $contatoEmergenciaCelular;
    public $estado;
    public $numero;
    public $complemento;
    public $mae;
    public $num_titulo;
    public $zona;
    public $secao;
    public $num_ctps;
    public $serie_ctps;
    public $dt_ctps;
    public $num_termo;
    public $folha;
    public $livro;
    public $nome_cart;
    public $emissao_cert;
    public $cor;
    public $nacionalidade;
    public $municipio_nascimento;
    protected $dao;
    public $cidade_nasc;

    protected $_tabela = array(
        'nome' => 'recepcao_func_cadastro',
        'chave_primaria' => array('codigo'),
        'colunas' => array(
            'matricula',
            'nome',
            'funcao',
            'endereco',
            'bairro',
            'numero',
            'complemento',
            'cidade',
            'naturalidade',
            'estado',
            'cep',
            'telefone',
            'estadoCivil',
            'conjuge',
            'cartprof',
            'cpf',
            'nascimento',
            'admissao',
            'turno',
            'sexo',
            'celular',
            'codhora',
            'dtsaida',
            'num_ident',
            'uf_identidade',
            'compl_ident',
            'dt_ident',
            'placa_carro',
            'carro',
            'email',
            'pis',
            'emailColegio',
            'emailColegio2',
            'contatoEmergenciaNome',
            'contatoEmergenciaCelular',
            'mae',
            'num_titulo',
            'zona',
            'secao',
            'num_ctps',
            'serie_ctps',
            'dt_ctps',
            'num_termo',
            'folha',
            'livro',
            'nome_cart',
            'emissao_cert',
            'cor',
            'nacionalidade',
            'municipio_nascimento'
        )
    );

    public function __construct($cod = NULL)
    {
        if (!empty($cod)) {
            $this->codigo = $cod;
            $this->carregar();
        }
    }

    public function pesquisar()
    {
        $this->queryCorrente = "SELECT p.*
                                FROM recepcao_func_cadastro p 
                                WHERE 1=1 ";
        try {
            $result = $this->buscar();
            return $result;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function obterFoto()
    {
        if (empty($this->matricula)) {
            return '';
        }
        /* $dirname = 'img/funcionario/'; */
        $dirname = 'funcionarios/online/principal/_fotos_funcionario/';
        for ($i = 0; $i < 5; $i++) {
            if (is_dir($dirname)) {
                break;
            }
            $i++;
            $dirname = '../' . $dirname;
        }

        //$dir_foto = dirname(dirname(dirname(__FILE__))) .'/secretaria/online/_alunos_hires/' ;
        $_tipos = array('.jpg', '.JPG', '.jpeg', '.JPEG', '.png', '.PNG');
        foreach ($_tipos as $tipo) {
            $filename = $dirname . $this->matricula . $tipo;
            if (file_exists($filename)) {
                return $filename;
            }
        }
        /*         return str_replace('img/funcionario/', 'img/funcionario/', $dirname) . 'funcionario_sem_foto.png';*/
        return str_replace('professores/online/principal/_fotos_funcionario/', 'secretaria/online/_alunos_hires/', $dirname) . 'funcionario_sem_foto.png';
    }

    public function deletarFoto()
    {
        $matricula = $this->tx_img_delete;
        if ($matricula) {
            $novoNomeArquivo = $matricula . ".jpg";
            $diretorioDestino = realpath(__DIR__ . 'funcionarios/online/principal/_fotos_funcionario/');

            $destino = $diretorioDestino . '\\' . $novoNomeArquivo;

            if (file_exists($destino)) {
                unlink($destino);
            }
        }
    }

    public static function obterDaSessao(){
        if(!isset($_SESSION['PO_funcionarioCPF']) OR empty($_SESSION['PO_funcionarioCPF'])){
            return FALSE;
        }
        $funcionario = new Funcionario();
        $cpf = str_replace(array('.', '-'), '', $_SESSION['PO_funcionarioCPF']);
        $funcionario->filtrar('cpf', $cpf);
        $_result = $funcionario->pesquisar();
        if(count($_result['dados']) > 0){
            return $_result['dados'][0];
        }
        //throw new Exception('Não foi possível identificar os dados do Professor.');
        return FALSE;
    }

    public function salvarFoto()
    {
        if (isset($_FILES['tx_image']) && $_FILES['tx_image']['error'] === UPLOAD_ERR_OK) {
            $fotoTmp = $_FILES['tx_image']['tmp_name'];
            $fotoNome = $_FILES['tx_image']['name'];
            $fotoExtensao = strtolower(pathinfo($fotoNome, PATHINFO_EXTENSION));
            $matricula = $this->matricula;

            $extensoesPermitidas = array('jpg', 'jpeg', 'png', 'gif', 'webp');

            if (!in_array($fotoExtensao, $extensoesPermitidas)) {
                return json_encode(array("success" => false, "message" => "Formato de arquivo inválido! Apenas imagens são permitidas."));
            }

            $novoNomeArquivo = $matricula . ".jpg";

            $diretorioDestino = realpath(__DIR__ . 'funcionarios/online/principal/_fotos_funcionario/');

            $destino = $diretorioDestino . '\\' . $novoNomeArquivo;

            if (file_exists($destino)) {
                unlink($destino);
            }

            switch ($fotoExtensao) {
                case 'png':
                    $imagem = imagecreatefrompng($fotoTmp);
                    break;
                case 'gif':
                    $imagem = imagecreatefromgif($fotoTmp);
                    break;
                case 'webp':
                    $imagem = imagecreatefromwebp($fotoTmp);
                    break;
                default: // jpg ou jpeg
                    $imagem = imagecreatefromjpeg($fotoTmp);
                    break;
            }

            if (!$imagem) {
                return json_encode(array("success" => false, "message" => "Erro ao processar a imagem."));
            }

            if (imagejpeg($imagem, $destino, 90)) {
                imagedestroy($imagem); // Liberar memória
                return json_encode(array("success" => true, "message" => "Foto enviada com sucesso!", "path" => $destino));
            } else {
                imagedestroy($imagem);
                return json_encode(array("success" => false, "message" => "Erro ao salvar a imagem convertida."));
            }
        } else {
            return json_encode(array("success" => false, "message" => "Nenhuma foto enviada ou erro no upload."));
        }
    }

}

