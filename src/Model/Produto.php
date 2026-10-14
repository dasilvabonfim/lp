<?php

declare(strict_types=1);

namespace MiniForm\Model;

use MiniForm\Attributes\Field;
use MiniForm\Attributes\Table;

#[Table('produtos')]
final class Produto extends Model
{
    #[Field(
        label: 'Nome do produto',
        required: true,
        maxLength: 100,
        placeholder: 'Ex.: Teclado mecânico',
    )]
    public string $nome = '';

    #[Field(
        label: 'Categoria',
        type: 'select',
        required: true,
        options: [
            'eletronicos' => 'Eletrônicos',
            'livros' => 'Livros',
            'casa' => 'Casa e decoração',
            'esportes' => 'Esportes',
        ],
    )]
    public string $categoria = 'eletronicos';

    #[Field(
        label: 'Descrição',
        type: 'textarea',
        required: true,
        maxLength: 500,
        placeholder: 'Descreva as principais características',
    )]
    public string $descricao = '';

    #[Field(
        label: 'Preço (R$)',
        type: 'number',
        sqlType: 'REAL',
        required: true,
        min: 0,
        step: '0.01',
        placeholder: '0,00',
    )]
    public float $preco = 0.0;

    #[Field(
        label: 'Forma de envio',
        type: 'radio',
        required: true,
        options: [
            'normal' => 'Normal',
            'expresso' => 'Expresso',
            'retirada' => 'Retirada no local',
        ],
    )]
    public string $envio = 'normal';

    #[Field(
        label: 'Produto disponível',
        type: 'checkbox',
        sqlType: 'INTEGER',
    )]
    public bool $disponivel = true;
}

