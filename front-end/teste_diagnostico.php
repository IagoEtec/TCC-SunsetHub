<?php
require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
require_once("../back-end/conexao.php");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

try {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN patente VARCHAR(30) NULL AFTER banner");
} catch (PDOException $ignored) {
    // A coluna já existe ou será criada pela migration_patente.sql.
}
try {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN xp INT NOT NULL DEFAULT 0 AFTER patente");
} catch (PDOException $ignored) {
    // A coluna já existe ou será criada pela migration_xp.sql.
}

// Questões atuais: investimentos, gestão financeira e criptoativos.
$questoes = [
    [
        'pergunta' => 'Qual é a primeira etapa antes de escolher um investimento?',
        'alternativas' => [
            'Definir objetivo, prazo e tolerância a risco',
            'Seguir a indicação mais comentada',
            'Escolher sempre o ativo que mais subiu',
            'Investir sem montar orçamento',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'Qual atitude ajuda a montar uma reserva de emergência?',
        'alternativas' => [
            'Usar ativos de alta liquidez e baixo risco',
            'Concentrar tudo em criptomoedas',
            'Escolher apenas ativos sem resgate',
            'Investir somente em ações',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'O que é diversificação de carteira?',
        'alternativas' => [
            'Distribuir recursos entre ativos e classes diferentes',
            'Comprar o mesmo ativo em várias corretoras',
            'Colocar tudo no investimento mais rentável',
            'Manter todo o dinheiro parado',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'Se a inflação sobe e o dinheiro fica parado, o que pode ocorrer?',
        'alternativas' => [
            'O poder de compra pode diminuir',
            'O saldo nominal aumenta sozinho',
            'O dinheiro passa a render automaticamente',
            'O risco desaparece',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'O que representa a liquidez de um investimento?',
        'alternativas' => [
            'A facilidade e a rapidez para transformar o ativo em dinheiro',
            'A garantia de lucro diário',
            'O tamanho da empresa emissora',
            'A quantidade de dividendos',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'Qual é uma diferença importante entre rentabilidade nominal e real?',
        'alternativas' => [
            'A real considera o efeito da inflação',
            'A nominal sempre é menor',
            'A real ignora custos e inflação',
            'Não existe diferença',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'O que é volatilidade?',
        'alternativas' => [
            'A intensidade das oscilações de preço de um ativo',
            'A certeza de receber juros',
            'O prazo de vencimento de uma conta',
            'A taxa de câmbio fixa',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'Sobre criptomoedas, qual afirmação é mais responsável?',
        'alternativas' => [
            'Podem ter alta volatilidade e exigem gestão de risco',
            'São sempre protegidas pelo FGC',
            'Não sofrem oscilações',
            'Garantem retorno positivo',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'O que é uma stablecoin?',
        'alternativas' => [
            'Um criptoativo projetado para acompanhar o valor de uma referência',
            'Uma ação de empresa estatal',
            'Um título público brasileiro',
            'Uma moeda sem qualquer risco',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'Por que não se deve compartilhar a chave privada de uma carteira cripto?',
        'alternativas' => [
            'Quem a possui pode controlar os ativos',
            'Ela serve apenas para receber promoções',
            'Ela reduz a inflação',
            'Ela garante lucro',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'Qual é um risco de deixar toda a carteira em um único ativo?',
        'alternativas' => [
            'A concentração aumenta o impacto de um problema nesse ativo',
            'A liquidez sempre melhora',
            'O risco é eliminado',
            'A rentabilidade fica garantida',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'O que é custo de oportunidade?',
        'alternativas' => [
            'O benefício que poderia ser obtido com uma alternativa escolhida em vez de outra',
            'Uma tarifa obrigatória da bolsa',
            'O imposto pago em toda compra',
            'A taxa fixa de uma poupança',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'Ao comparar um CDB, qual item deve ser analisado além da taxa?',
        'alternativas' => [
            'Liquidez, prazo, emissor e cobertura aplicável',
            'A cor do aplicativo',
            'A quantidade de propagandas',
            'Somente o nome do banco',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'O que caracteriza uma dívida saudável no orçamento?',
        'alternativas' => [
            'Parcela compatível com a renda e finalidade planejada',
            'Qualquer parcela que caiba no primeiro mês',
            'Empréstimo sem comparar juros',
            'Usar crédito para pagar todas as despesas',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'Qual prática melhora a gestão financeira mensal?',
        'alternativas' => [
            'Registrar receitas, despesas e metas',
            'Ignorar gastos pequenos',
            'Misturar dinheiro pessoal e crédito sem controle',
            'Investir antes de pagar contas essenciais',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'O que significa rebalancear uma carteira?',
        'alternativas' => [
            'Ajustar os pesos dos ativos para voltar ao plano definido',
            'Trocar todo investimento por cripto',
            'Vender sempre quando houver queda',
            'Comprar apenas o ativo mais caro',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'Uma ação caiu 20% em um dia. Qual reação é mais prudente?',
        'alternativas' => [
            'Reavaliar fundamentos, objetivo e risco antes de decidir',
            'Vender tudo por impulso',
            'Comprar com todo o patrimônio',
            'Assumir que irá subir com certeza',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'O que são juros compostos?',
        'alternativas' => [
            'Rendimentos que também passam a render ao longo do tempo',
            'Juros cobrados apenas uma vez',
            'Uma taxa sem relação com prazo',
            'Um desconto de corretagem',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'Qual é uma boa prática de segurança para investimentos digitais?',
        'alternativas' => [
            'Usar autenticação forte e desconfiar de promessas de lucro garantido',
            'Enviar senhas por mensagem',
            'Usar a mesma senha em todos os serviços',
            'Clicar em qualquer link de suporte',
        ],
        'correta' => 0
    ],
    [
        'pergunta' => 'Por que rentabilidade passada não garante resultado futuro?',
        'alternativas' => [
            'Mercados mudam e os resultados dependem de riscos e condições futuras',
            'Porque todo investimento perde dinheiro',
            'Porque gráficos são sempre falsos',
            'Porque apenas a inflação importa',
        ],
        'correta' => 0
    ],
];

// Variáveis para controle do teste
$teste_finalizado = false;
$pontuacao = 0;
$patente = "";
$diagnostico = "";

// Processa o envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar'])) {
    // CSRF: sem isso, outro site poderia forçar o envio do teste
    // diagnóstico em nome da vítima, alterando a patente dela.
    csrf_exigir();

    $pontuacao = 0;
    $total_questoes = count($questoes);
    
    for ($i = 0; $i < $total_questoes; $i++) {
        $resposta = isset($_POST['q' . $i]) ? intval($_POST['q' . $i]) : -1;
        if ($resposta === $questoes[$i]['correta']) {
            $pontuacao++;
        }
    }
    
    // O diagnóstico libera no máximo Ouro 1; o restante da progressão ocorre na Arena.
    if ($pontuacao <= 4) {
        $patente = "FERRO 1";
        $diagnostico = "Você está começando: vamos construir uma base financeira segura.";
        $xpInicial = 0;
    } elseif ($pontuacao <= 9) {
        $patente = "FERRO 2";
        $diagnostico = "Você já reconhece conceitos importantes e pode evoluir com prática.";
        $xpInicial = 50;
    } elseif ($pontuacao <= 14) {
        $patente = "FERRO 3";
        $diagnostico = "Você tem uma boa base para estudar carteira, risco e planejamento.";
        $xpInicial = 150;
    } else {
        $patente = "OURO 1";
        $diagnostico = "Você demonstrou domínio dos fundamentos. Continue praticando com responsabilidade.";
        $xpInicial = 300;
    }
    
    // Salva o resultado no banco (opcional)
    try {
        $sql = "INSERT INTO resultados_teste (usuario_id, pontuacao, patente, diagnostico, data_teste) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['id'], $pontuacao, $patente, $diagnostico]);
    } catch (PDOException $e) {
        // Se a tabela não existir, apenas ignora
    }

    $stmtPatente = $pdo->prepare("UPDATE usuarios SET patente = ?, xp = GREATEST(COALESCE(xp, 0), ?) WHERE id = ?");
    $stmtPatente->execute([$patente, $xpInicial, $_SESSION['id']]);
    
    $teste_finalizado = true;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Diagnóstico</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* =========================
           ESTILOS DO TESTE DIAGNÓSTICO
           Liquid Glass + tema escuro
        ========================= */
        
        .hero_teste {
            padding-top: 120px;
            min-height: 50vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0A2540 0%, #24314C 50%, #0A2540 100%);
            position: relative;
            overflow: hidden;
        }

        .hero_teste::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(22, 226, 138, 0.06);
            right: -150px;
            top: -100px;
            filter: blur(120px);
        }

        .hero_teste_container {
            width: 100%;
            max-width: 1300px;
            margin: auto;
            padding: 60px 40px 40px;
            position: relative;
            z-index: 2;
        }

        .hero_teste_content {
            max-width: 750px;
        }

        .badge_teste {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 9999px;
            background: rgba(22, 226, 138, 0.12);
            color: #16E28A;
            border: 1px solid rgba(22, 226, 138, 0.25);
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }

        .hero_teste_content h1 {
            font-size: clamp(2.8rem, 5vw, 4rem);
            line-height: 1.15;
            color: #ffffff;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .hero_teste_content h1 .destaque {
            color: #16E28A;
            background: linear-gradient(135deg, #16E28A, #0db873);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero_teste_content p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.15rem;
            line-height: 1.8;
            max-width: 600px;
        }

        /* Container do teste */
        .teste_container {
            max-width: 900px;
            margin: -30px auto 60px;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        .teste_card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.20), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .progresso_teste {
            margin-bottom: 30px;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .progresso_teste span {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
        }

        .progresso_teste strong {
            color: #16E28A;
        }

        .questao {
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .questao:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .questao_numero {
            display: inline-block;
            background: rgba(22, 226, 138, 0.15);
            color: #16E28A;
            padding: 4px 14px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .questao p {
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .alternativas {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .alternativa {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 14px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .alternativa:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(22, 226, 138, 0.3);
        }

        .alternativa input[type="radio"] {
            accent-color: #16E28A;
            width: 18px;
            height: 18px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .alternativa label {
            color: rgba(255, 255, 255, 0.85);
            cursor: pointer;
            font-size: 0.95rem;
        }

        /* Botão finalizar */
        .btn_finalizar {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 9999px;
            background: linear-gradient(135deg, #16E28A, #29f0a0);
            color: #0A2540;
            font-weight: 800;
            font-size: 1.1rem;
            cursor: pointer;
            transition: 0.3s ease;
            box-shadow: 0 8px 24px rgba(22, 226, 138, 0.30);
            margin-top: 20px;
        }

        .btn_finalizar:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(22, 226, 138, 0.45);
        }

        /* =========================
           RESULTADO
        ========================= */
        .resultado_box {
            text-align: center;
            padding: 30px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .resultado_icone {
            font-size: 5rem;
            margin-bottom: 15px;
        }

        .resultado_box h2 {
            color: #ffffff;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .resultado_patente {
            display: inline-block;
            padding: 8px 24px;
            background: linear-gradient(135deg, #16E28A, #29f0a0);
            color: #0A2540;
            border-radius: 9999px;
            font-size: 1.5rem;
            font-weight: 800;
            margin: 15px 0;
        }

        .resultado_box p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .resultado_pontuacao {
            color: #16E28A;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .btn_voltar_recursos {
            display: inline-block;
            padding: 14px 35px;
            border: none;
            border-radius: 9999px;
            background: #16E28A;
            color: #0A2540;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s ease;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(22, 226, 138, 0.25);
        }

        .btn_voltar_recursos:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(22, 226, 138, 0.40);
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .hero_teste {
                min-height: 35vh;
                padding-top: 100px;
            }
            .hero_teste_container {
                padding: 40px 20px;
            }
            .hero_teste_content h1 {
                font-size: 2.2rem;
            }
            .teste_card {
                padding: 25px 20px;
            }
            .alternativa {
                padding: 10px 14px;
            }
            .resultado_patente {
                font-size: 1.2rem;
            }
        }
    </style>
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">
    <link rel="stylesheet" href="../css/diagnostico.css?v=1">

</head>
<body class="diagnostico-page">

<?php include_once 'navbar.php'; ?>

<main>

    <!-- HERO -->
    <section class="hero_teste">
        <div class="hero_teste_container">
            <div class="hero_teste_content">
                <span class="badge_teste">
                    <i class="fas fa-clipboard-check"></i> Teste Diagnóstico
                </span>
                <h1>Descubra seu <span class="destaque">Perfil de Investidor</span></h1>
                <p>Responda as 20 questões abaixo e descubra seu nível de conhecimento sobre investimentos.</p>
            </div>
        </div>
    </section>

    <!-- CONTEÚDO DO TESTE -->
    <section class="teste_container">
        <div class="teste_card">
            
            <?php if (!$teste_finalizado): ?>
            
            <!-- Formulário do teste -->
            <form method="POST" action="">
                <?= csrf_campo() ?>
                <div class="progresso_teste">
                    <span><i class="fas fa-info-circle"></i> Responda todas as questões. Cada questão vale <strong>1 ponto</strong>.</span>
                </div>
                
                <?php foreach ($questoes as $index => $q): ?>
                <div class="questao">
                    <div class="questao_numero">Questão <?php echo $index + 1; ?></div>
                    <p><?php echo $q['pergunta']; ?></p>
                    <div class="alternativas">
                        <?php foreach ($q['alternativas'] as $alt_index => $alt): ?>
                        <div class="alternativa">
                            <input type="radio" name="q<?php echo $index; ?>" id="q<?php echo $index . '_' . $alt_index; ?>" value="<?php echo $alt_index; ?>" required>
                            <label for="q<?php echo $index . '_' . $alt_index; ?>"><?php echo $alt; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <button type="submit" name="finalizar" class="btn_finalizar">
                    <i class="fas fa-arrow-right"></i> Finalizar Teste
                </button>
            </form>
            
            <?php else: ?>
            
            <!-- Resultado -->
            <div class="resultado_box">
                <div class="resultado_icone">
                    <?php if ($pontuacao <= 4): ?>
                        <i class="fas fa-graduation-cap" style="color: #FF4757;"></i>
                    <?php elseif ($pontuacao <= 14): ?>
                        <i class="fas fa-graduation-cap" style="color: #FFA502;"></i>
                    <?php else: ?>
                        <i class="fas fa-graduation-cap" style="color: #16E28A;"></i>
                    <?php endif; ?>
                </div>
                <h2>Teste Finalizado!</h2>
                <div class="resultado_patente"><?php echo $patente; ?></div>
                <p><?php echo $diagnostico; ?></p>
                <div class="resultado_pontuacao">
                    <i class="fas fa-star"></i> Pontuação: <?php echo $pontuacao; ?> / 20
                </div>
                <!-- Link para voltar - agora apontando para aprendizado.php na mesma pasta -->
                <a href="aprendizado.php" class="btn_voltar_recursos">
                    <i class="fas fa-arrow-left"></i> Voltar para Recursos
                </a>
            </div>
            
            <?php endif; ?>
            
        </div>
    </section>

</main>

<script>
    // Marca a alternativa automaticamente ao clicar em toda a div
    document.querySelectorAll('.alternativa').forEach(function(el) {
        el.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });
</script>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>
</html>
