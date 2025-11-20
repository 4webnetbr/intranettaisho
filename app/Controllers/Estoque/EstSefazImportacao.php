<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Models\Config\ConfigEmpresaModel;
use App\Services\SefazService;
use CodeIgniter\API\ResponseTrait;

class EstSefazImportacao extends BaseController
{
    use ResponseTrait;

    /**
     * @var ConfigEmpresaModel
     */
    protected $empresaModel;

    /**
     * @var SefazService
     */
    protected $sefazService;

    public function __construct()
    {
        $this->empresaModel = new ConfigEmpresaModel();
        $this->sefazService = new SefazService();
    }

    /**
     * Importa NFe de uma empresa específica ou de todas as empresas ativas.
     *
     * Exemplos de uso:
     *  - /estoque/nfe/importar/5      → importa apenas da empresa ID 5
     *  - /estoque/nfe/importar        → importa de todas as empresas elegíveis
     *
     * @param int|null $emp_id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function importar($emp_id = null)
    {
        // Se foi informado um emp_id específico → processa só ele
        if (!empty($emp_id)) {
            return $this->importarEmpresaUnica((int) $emp_id);
        }

        // Senão → processa TODAS as empresas elegíveis
        return $this->importarTodasEmpresas();
    }

    /**
     * Importa NFe para UMA empresa específica.
     *
     * @param int $emp_id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function importarEmpresaUnica(int $emp_id)
    {
        $empresa = $this->empresaModel->find($emp_id);

        if (!$empresa) {
            return $this->failNotFound("Empresa ID {$emp_id} não encontrada.");
        }

        // Aqui você pode, se quiser, validar se a empresa está apta:
        // - tem CNPJ
        // - tem UF
        // - tem certificado configurado
        // - tem ambiente SEFAZ
        // Mas isso é opcional; o SefazService também valida e lança exceção.

        try {
            $resumo = $this->sefazService->consultarNSU($emp_id);

            return $this->respond([
                'status'  => 'ok',
                'empresa' => [
                    'emp_id'      => $empresa['emp_id'],
                    'emp_nome'    => $empresa['emp_nome'] ?? '',
                    'emp_cnpj'    => $empresa['emp_cnpj'] ?? '',
                    'emp_uf'      => $empresa['emp_uf'] ?? '',
                ],
                'resumo'  => $resumo,
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Erro ao importar NFe da empresa ' . $emp_id . ': ' . $e->getMessage());

            return $this->failServerError(
                "Erro ao importar NFe da empresa {$emp_id}: " . $e->getMessage()
            );
        }
    }

    /**
     * Importa NFe para TODAS as empresas elegíveis.
     *
     * Critérios de exemplo:
     *  - emp_status em (1,2)  → Ativo / Pré-cadastrado
     *  - (Opcional) algum campo tipo emp_sefaz_ativo = 1, se você tiver isso
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function importarTodasEmpresas()
    {
        // Filtro básico: empresas ativas / pré-cadastradas
        $builder = $this->empresaModel->where('emp_cert_tipo !=', NULL);

        // Se você tiver um campo de flag, pode descomentar:
        // $builder->where('emp_sefaz_ativo', 1);

        $empresas = $builder->findAll();

        if (empty($empresas)) {
            return $this->respond([
                'status'   => 'ok',
                'mensagem' => 'Nenhuma empresa elegível para importação de NFe.',
                'resumos'  => [],
            ]);
        }

        $resumos = [];
        foreach ($empresas as $empresa) {
            $emp_id = (int) $empresa['emp_id'];

            try {
                $resumo = $this->sefazService->consultarNSU($emp_id);

                $resumos[] = [
                    'emp_id'   => $empresa['emp_id'],
                    'emp_nome' => $empresa['emp_nome'] ?? '',
                    'ok'       => true,
                    'resumo'   => $resumo,
                ];
            } catch (\Throwable $e) {
                log_message('error', 'Erro ao importar NFe da empresa ' . $emp_id . ': ' . $e->getMessage());

                $resumos[] = [
                    'emp_id'   => $empresa['emp_id'],
                    'emp_nome' => $empresa['emp_nome'] ?? '',
                    'ok'       => false,
                    'erro'     => $e->getMessage(),
                ];
            }
        }

        return $this->respond([
            'status'   => 'ok',
            'totalEmpresas' => count($empresas),
            'resumos'  => $resumos,
        ]);
    }
}
