# Evolução do perfil — versão 67

Este documento registra a evolução do fluxo de perfil. A entrega é um pacote cumulativo: a versão 67 contém as melhorias da versão 1, da versão 2 e os refinamentos seguintes. As versões intermediárias representam checkpoints de engenharia e usabilidade, não 67 arquivos ZIP independentes.

| Versão | Evolução | Justificativa |
|---:|---|---|
| 1 | Abertura confiável do pop-up de foto | O seletor passou a ser acionado por botão, clique no avatar e teclado, evitando dependência de um único label. |
| 2 | Editor de banner 3:1 | O banner passou a ter enquadramento próprio, em vez de ser enviado sem ajuste. |
| 3 | Inicialização após carregamento do DOM | Evita que `defer` ou carregamento variável impeça os listeners. |
| 4 | Validação de elementos obrigatórios | Falhas de marcação deixam aviso no console em vez de quebrar silenciosamente. |
| 5 | Foco acessível no avatar | O avatar pode ser acionado por Enter e Espaço. |
| 6 | Fechamento por Escape | Reduz o custo de recuperação quando o modal fica aberto. |
| 7 | Fechamento ao clicar fora | Mantém o fluxo rápido em telas grandes e pequenas. |
| 8 | Bloqueio de rolagem durante modal | Evita que o conteúdo de fundo se mova enquanto a imagem é editada. |
| 9 | Restauração da rolagem | O estado da página volta ao normal após cancelar ou salvar. |
| 10 | Validação de MIME e extensão | Evita aceitar arquivos renomeados incorretamente. |
| 11 | Limite de 8 MB no navegador | Dá retorno imediato antes do envio. |
| 12 | Tratamento de erro do FileReader | Arquivos ilegíveis deixam mensagem compreensível. |
| 13 | Tratamento de erro do Image | Imagens incompatíveis não abrem um modal vazio. |
| 14 | Prévia em JPEG | Reduz o tamanho do payload enviado ao servidor. |
| 15 | Canvas quadrado de saída | Garante dimensão 1:1 para o avatar. |
| 16 | Canvas 3:1 de saída | Garante o formato horizontal esperado do banner. |
| 17 | Ajuste inicial automático | A imagem começa enquadrada sem exigir arraste manual. |
| 18 | Zoom incremental do avatar | Permite aproximar rostos e detalhes. |
| 19 | Zoom incremental do banner | Permite preencher o cabeçalho com imagens de proporções diferentes. |
| 20 | Posicionamento por ponteiro | Mouse, toque e caneta usam o mesmo fluxo. |
| 21 | Captura de ponteiro | O arraste não se perde ao sair brevemente da área. |
| 22 | Cursor de arraste | Comunica visualmente que a área pode ser movimentada. |
| 23 | Prévia circular do avatar | Mostra o resultado no formato em que o site exibe a foto. |
| 24 | Prévia horizontal do banner | Mostra o resultado próximo ao cabeçalho real. |
| 25 | Botões de cancelar | Permite desistir sem alterar o arquivo atual. |
| 26 | Botões de aplicar | Separa edição de confirmação. |
| 27 | Estado “Salvando…” | Evita submissões repetidas no avatar. |
| 28 | Estado hidden controlado | O modal não ocupa espaço quando fechado. |
| 29 | `aria-hidden` sincronizado | Leitores de tela não recebem conteúdo invisível. |
| 30 | Título associado ao diálogo | Melhora a navegação sem visão. |
| 31 | Rótulos associados aos sliders | O zoom é identificável por tecnologia assistiva. |
| 32 | Feedback percentual do zoom | O usuário sabe exatamente o nível aplicado. |
| 33 | Foco visual no avatar | O elemento acionável fica evidente ao navegar por Tab. |
| 34 | Foco visível de alta legibilidade | Mantém contraste no tema escuro. |
| 35 | Animação curta do modal | Dá continuidade visual sem atrasar o uso. |
| 36 | `overscroll-behavior` | Evita gestos que escapem para a página de fundo. |
| 37 | Layout em duas colunas | Aproveita telas largas sem esconder a prévia. |
| 38 | Layout de uma coluna em tablet | Preserva legibilidade em espaços médios. |
| 39 | Layout de uma coluna em celular | Evita controles comprimidos. |
| 40 | Área de trabalho responsiva | Canvas acompanha a largura disponível. |
| 41 | Botões empilháveis | Ações continuam fáceis de tocar no celular. |
| 42 | Fallback de erro no console | Diagnóstico de instalação fica mais rápido. |
| 43 | Fallback de upload do avatar | O envio ainda tem caminho claro quando a edição falha. |
| 44 | Remoção da dependência de GD | O avatar recortado pelo navegador não exige reprocessamento GD. |
| 45 | Verificação de JPEG quadrado no servidor | O servidor mantém a regra 1:1 mesmo se o cliente for alterado. |
| 46 | Verificação de proporção do banner | O servidor rejeita banners fora do formato esperado. |
| 47 | Criação automática da pasta de perfis | Evita falha em instalações novas. |
| 48 | Criação automática da pasta de banners | Evita falha no primeiro banner. |
| 49 | Verificação de escrita em `uploads/perfis` | Transforma erro genérico em orientação de permissão. |
| 50 | Verificação de escrita em `uploads/banners` | Explica a causa mais comum no Apache/XAMPP. |
| 51 | Nomes únicos com aleatoriedade | Evita colisões quando dois uploads ocorrem no mesmo segundo. |
| 52 | Limpeza do arquivo antigo | Reduz arquivos órfãos após troca de foto. |
| 53 | Limpeza do banner antigo | Reduz crescimento desnecessário da pasta de banners. |
| 54 | Redirecionamento pós-sucesso | O usuário volta ao perfil já atualizado. |
| 55 | Mensagens específicas de upload | Diferencia arquivo ausente, grande e sem permissão. |
| 56 | Registro de exceções no log | Ajuda a diagnosticar o ambiente sem expor detalhes ao usuário. |
| 57 | Migração da coluna `banner` | Bancos novos já possuem o campo necessário. |
| 58 | Migração compatível com banco antigo | O perfil tenta preparar o campo sem exigir recriação do banco. |
| 59 | Arquivo `migration_perfil.sql` | Existe uma alternativa explícita para administradores. |
| 60 | Caminhos físicos com `__DIR__` | Upload não depende do diretório de execução do Apache. |
| 61 | Caminhos web relativos corrigidos | Imagens salvas são exibidas corretamente a partir de `front-end`. |
| 62 | Cache busting do editor | O navegador busca a correção mesmo quando mantém JS antigo em cache. |
| 63 | Separação avatar/banner | Uma falha no editor de banner não impede a foto e vice-versa. |
| 64 | Editor de banner sem submissão imediata | O usuário consegue enquadrar antes de salvar. |
| 65 | Fallback de compatibilidade do formulário | O servidor mantém validação mesmo em envio sem recorte. |
| 66 | Testes estáticos de integração | Referências, scripts e arquivos de destino são verificados antes do pacote. |
| 67 | Pacote cumulativo final | Reúne usabilidade, acessibilidade, responsividade, segurança de upload e documentação em uma versão instalável. |

## Critérios de aceite da versão 67

A versão final deve abrir o modal ao clicar em **Alterar foto** ou no avatar, aceitar teclado, mostrar uma imagem carregada, permitir zoom e arraste, produzir JPEG quadrado e voltar ao perfil após salvar. O banner deve abrir um editor horizontal, permitir zoom e arraste, produzir uma imagem 3:1 e salvar em `uploads/banners`. O projeto também deve informar erros de permissão ou tamanho em linguagem clara.

| 68 | Zoom mínimo de 50%, canvas de maior resolução, arraste limitado às bordas da imagem, fundo de preenchimento derivado da própria foto, avatar visual maior e banner com saída 1800×600 | Permite afastar o enquadramento sem deixar áreas vazias, impede que a imagem escape pelos cantos e melhora a nitidez percebida após o salvamento. |
