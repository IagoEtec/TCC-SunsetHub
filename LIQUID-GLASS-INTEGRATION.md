# Integração Liquid Glass no AFDE

A referência Liquid Glass foi traduzida para a arquitetura PHP existente sem substituir o backend, as sessões ou os formulários. O projeto agora possui uma camada visual compartilhada em `css/liquid-glass.css` e uma camada de interação em `JS/liquid-glass.js`, carregadas nas páginas públicas, autenticadas e administrativas.

## Princípios aplicados

A superfície utiliza **tint translúcido**, `backdrop-filter` com blur e saturação, borda especular dupla, brilho interno, sombras em camadas e conteúdo crisp por cima. Essa combinação segue o princípio do material de referência: o efeito não deve ser apenas uma caixa transparente, mas uma superfície com profundidade, borda luminosa e contraste suficiente para preservar leitura e interação.

O fundo global usa gradientes atmosféricos e blobs desfocados, enquanto cartões, cabeçalho, menus, botões, formulários, modais, dashboards e elementos de perfil compartilham as mesmas variáveis. A classe de tema `claro` ajusta automaticamente as cores, preservando o modo `escuro` e o modo `sistema` já existentes.

## Interação compartilhada

`JS/liquid-glass.js` marca as superfícies com `data-liquid-glass` e acompanha o ponteiro para atualizar a posição do brilho especular. Em dispositivos com `prefers-reduced-motion`, o acompanhamento é desativado e as transições são reduzidas. Assim, o efeito continua funcional sem impor movimento a quem solicitou redução de animação.

## Integração

O asset CSS e o script são carregados uma vez por página antes do fechamento de `head` e `body`, respectivamente. Os caminhos relativos foram calculados para as pastas `front-end`, `admin` e arquivos na raiz. A integração cobre 28 páginas PHP com HTML completo.

O sistema de perfil mantém seu editor de foto 1:1 e de banner 3:1. Eles também recebem o mesmo material Liquid Glass nos diálogos, mas preservam seus canvases e controles de edição para não misturar decoração com a área de manipulação da imagem.

## Compatibilidade

O efeito degrada de forma aceitável quando `backdrop-filter` não está disponível: a cor translúcida, as bordas, os gradientes e as sombras continuam presentes. A camada não depende do runtime React do material de referência, pois este projeto é servido pelo XAMPP como PHP tradicional.
