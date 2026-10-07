import os, re, glob

SNIPPET = '\n    <!-- Widget de Acessibilidade — integrado em todas as páginas -->\n    <script src="{path}" defer></script>\n</body>'

targets = []
for f in glob.glob('front-end/*.php'):
    targets.append((f, '../JS/acessibilidade.js'))
for f in glob.glob('admin/*.php'):
    targets.append((f, '../JS/acessibilidade.js'))
for f in ['teste-brapi.php']:
    if os.path.exists(f):
        targets.append((f, 'JS/acessibilidade.js'))

changed = []
no_body = []

for path, rel in targets:
    with open(path, encoding='utf-8') as fh:
        content = fh.read()
    if '</body>' not in content:
        no_body.append(path)
        continue
    if 'acessibilidade.js' in content:
        continue  # already has it
    # Insert before the LAST </body> occurrence
    idx = content.rfind('</body>')
    new_content = content[:idx] + SNIPPET.format(path=rel) + content[idx+len('</body>'):]
    with open(path, 'w', encoding='utf-8') as fh:
        fh.write(new_content)
    changed.append(path)

print(f"Injected into {len(changed)} files:")
for c in changed:
    print(" ", c)
print(f"\nNo </body> found (skipped) {len(no_body)}:", no_body)

