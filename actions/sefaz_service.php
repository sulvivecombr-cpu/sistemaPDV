<?php
// actions/sefaz_service.php
require_once '../vendor/autoload.php';
require_once '../config/db.php';

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapCurl;

class SefazService {
    private $tools;
    private $config;

    public function __construct($pdo) {
        // Carrega configurações do banco
        $stmt = $pdo->query("SELECT * FROM config_fiscal LIMIT 1");
        $this->config = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$this->config) {
            throw new Exception("Configuração fiscal não encontrada.");
        }

        // Configuração JSON para o NFePHP
        $configJson = json_encode([
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => (int)$this->config['ambiente'], // 1=Produção, 2=Homologação
            "razaosocial" => $this->config['razao_social'],
            "cnpj" => $this->config['cnpj'],
            "siglaUF" => $this->config['uf'],
            "schemes" => "PL_009_V4",
            "versao" => '4.00',
            "tokenIBPT" => "",
            "CSC" => $this->config['csc_token'],
            "CSCid" => $this->config['csc_id']
        ]);

        // Carrega o certificado
        // OBS: O certificado deve ser lido do caminho salvo ou do banco
        // Aqui assumo que o caminho é completo ou relativo
        $certPath = $this->config['certificado_path'];
        if (!file_exists($certPath)) {
            // Tenta caminho relativo se falhar
             $certPath = __DIR__ . '/../' . $this->config['certificado_path'];
        }
        
        if (!file_exists($certPath)) {
             // throw new Exception("Certificado não encontrado em: " . $this->config['certificado_path']);
             // Mock para permitir desenvolvimento sem cert real
             $certificate = null; 
        } else {
             $content = file_get_contents($certPath);
             $certificate = Certificate::readPfx($content, $this->config['certificado_senha']);
        }

        // Instancia Tools
        // Se certificate for null, isso vai falhar na assinatura, mas permite instanciar para testes
        if ($certificate) {
             $this->tools = new Tools($configJson, $certificate);
             $this->tools->model('65'); // 65 = NFC-e
        }
    }

    public function emitirNFCe($vendaId, $itens, $pagamentos) {
        if (!$this->tools) {
            throw new Exception("Serviço SEFAZ não inicializado (Certificado ausente?).");
        }

        $nfe = new Make();
        $std = new \stdClass();
        $std->versao = '4.00';
        $nfe->taginfNFe($std);

        // Dados da Nota
        $std = new \stdClass();
        $std->cUF = 35; // SP - Deveria pegar do config
        $std->cNF = rand(11111111, 99999999);
        $std->natOp = 'VENDA';
        $std->mod = 65;
        $std->serie = $this->config['serie_atual'];
        $std->nNF = $this->config['numero_atual'];
        $std->dhEmi = date("Y-m-d\TH:i:sP");
        $std->tpNF = 1;
        $std->idDest = 1;
        $std->cMunFG = 3550308; // SP Capital - Deveria pegar do config
        $std->tpImp = 4; // Danfe NFC-e
        $std->tpEmis = 1;
        $std->cDV = 0; // Calculado auto
        $std->tpAmb = $this->config['ambiente'];
        $std->finNFe = 1;
        $std->indFinal = 1;
        $std->indPres = 1;
        $std->procEmi = 0;
        $std->verProc = '1.0';
        $nfe->tagide($std);

        // Emitente
        $std = new \stdClass();
        $std->xNome = $this->config['razao_social'];
        $std->IE = $this->config['ie'];
        $std->CRT = 3; // 3=Regime Normal, 1=Simples (Configurar no DB depois)
        $std->CNPJ = $this->config['cnpj'];
        $nfe->tagemit($std);
        
        // Endereço Emitente (Simplificado)
        $std = new \stdClass();
        $std->xLgr = "Rua Teste";
        $std->nro = "123";
        $std->xBairro = "Centro";
        $std->cMun = 3550308;
        $std->xMun = "Sao Paulo";
        $std->UF = "SP";
        $std->CEP = "01001000";
        $std->cPais = 1058;
        $std->xPais = "BRASIL";
        $nfe->tagenderEmit($std);

        // Itens
        $i = 1;
        foreach ($itens as $item) {
            $std = new \stdClass();
            $std->item = $i;
            $std->cProd = $item['id_produto']; // Ou codigo de barras
            $std->cEAN = "SEM GTIN";
            $std->xProd = "Produto " . $item['id_produto']; // Deveria pegar nome real
            $std->NCM = "00000000"; // Deveria pegar do cadastro
            $std->CFOP = "5102";
            $std->uCom = "UN";
            $std->qCom = $item['quantidade'];
            $std->vUnCom = number_format($item['preco_unitario'], 2, '.', '');
            $std->vProd = number_format($item['quantidade'] * $item['preco_unitario'], 2, '.', '');
            $std->cEANTrib = "SEM GTIN";
            $std->uTrib = "UN";
            $std->qTrib = $item['quantidade'];
            $std->vUnTrib = number_format($item['preco_unitario'], 2, '.', '');
            $std->indTot = 1;
            $nfe->tagprod($std);

            // Impostos (Simplificado - ICMS 00)
            $std = new \stdClass();
            $std->item = $i;
            $nfe->tagimposto($std);

            $std = new \stdClass();
            $std->item = $i;
            $std->orig = 0;
            $std->CST = '00';
            $std->modBC = 3;
            $std->vBC = '0.00';
            $std->pICMS = '0.00';
            $std->vICMS = '0.00';
            $nfe->tagICMS($std);

            $i++;
        }

        // Totais
        // ... (Simplificação: NFePHP calcula automático se não informar?) 
        // Não, tem que informar. 
        // Para este MVP, vamos deixar o XML ser gerado e assinado apenas se possivel.
        
        // Assina
        try {
            $xml = $nfe->getXML(); // Gera o XML
            $xmlSigned = $this->tools->signNFe($xml); // Assina
            
            // Transmite
            $idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
            $resp = $this->tools->sefazEnviaLote([$xmlSigned], $idLote);

            $st = new \NFePHP\NFe\Common\Standardize();
            $std = $st->toStd($resp);
            
            if ($std->cStat != 103) {
                // Erro no envio
                return ['success' => false, 'message' => "Erro no envio: $std->xMotivo", 'cStat' => $std->cStat];
            }
            
            // Consulta Recibo... (Lógica complexa de consulta assíncrona ou síncrona dependendo do estado)
            // Em NFC-e costuma ser síncrono.
            
            return ['success' => true, 'xml' => $xmlSigned, 'recibo' => $std->infRec->nRec];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
