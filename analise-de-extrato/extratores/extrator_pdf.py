import pdfplumber as pdftool

def buscar_texto(filepath):
    texto_completo = ""
    with pdftool.open(filepath) as tool:
        for numPag, pag in enumerate(tool.pages, 1):
            data = pag.extract_text() or ""
            texto_completo += f"\n--- página {numPag} ---\n{data}"
    return texto_completo
