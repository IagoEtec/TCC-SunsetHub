import pandas as pd

import pandas as pd

def buscar_texto(filepath):
    df = pd.read_csv(filepath, encoding='UTF-8')
    return df.to_string(index=False)
