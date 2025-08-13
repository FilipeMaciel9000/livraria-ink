<?php

/**
 * Projeto: Livraria INK
 * Descrição: Esta classe representa um livro no sistema de gerenciamento de estoque da Livraria INK.
 * Ela encapsula propriedades essenciais como título, autor, editora, quantidade em estoque, preço e status de classificação. 
 * Inclui também mecanismos de validação para garantir integridade e consistência dos dados.
 */

class Livro
{
    public const STATUS_VALIDOS = ['Comum', 'Raro', 'Coleção', 'Avulso', 'Autografado'];

    public ?int $id;
    public string $titulo;
    public string $autor;
    public string $editora;
    public int $quantidade;
    public float $preco;
    public string $status;

    /**
     * Construtor da classe Livro.
     *
     * @param int|null $id
     * @param string $titulo
     * @param string $autor
     * @param string $editora
     * @param int $quantidade
     * @param float $preco
     * @param string $status
     */
    public function __construct(
        ?int $id = null,
        string $titulo = '',
        string $autor = '',
        string $editora = '',
        int $quantidade = 0,
        float $preco = 0.0,
        string $status = 'Comum'
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->autor = $autor;
        $this->editora = $editora;
        $this->quantidade = $quantidade;
        $this->preco = $preco;
        $this->setStatus($status); // Usa o método para validar
    }

    /**
     * Define o status do livro, com validação.
     *
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = in_array($status, self::STATUS_VALIDOS) ? $status : 'Comum';
    }

    /**
     * Cria uma instância de Livro a partir de um array (ex: resultado do banco).
     *
     * @param array $data
     * @return Livro
     */
    public static function fromArray(array $data): Livro
    {
        return new Livro(
            $data['id'] ?? null,
            $data['titulo'] ?? '',
            $data['autor'] ?? '',
            $data['editora'] ?? '',
            isset($data['quantidade']) ? (int)$data['quantidade'] : 0,
            isset($data['preco']) ? (float)$data['preco'] : 0.0,
            $data['status'] ?? 'Comum'
        );
    }
}
