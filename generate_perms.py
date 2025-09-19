# generate_perms.py
from itertools import permutations

def generate_perms(letters):
    # vratí zoznam jedinečných permutácií ako stringy
    perms = [''.join(p) for p in permutations(letters)]
    return perms

def is_word_in_dictionary(word, slovnik):
    # jednoduchá kontrola proti slovniku (set)
    return word.lower() in slovnik

def main():
    # Verzia A: bez diakritiky
    letters_ascii = ['D','E','I','L','N']  # poradie môže byť ľubovoľné; tu poradie podľa vstupu
    perms_ascii = generate_perms(letters_ascii)

    # Vám môžeme pridať jednoduchý slovník slovenčina/čeština ako príklad
    # Príklad veľmi malý (len pre ilustráciu). Rozšíriť podľa potreby.
    slovnik_slovenčina = {
        'dilen','dieln','denil','ledi n'.replace(' ','')  # placeholder
    }

    # Verzia B: s diakritikou
    letters_diacrit = ['D','E','Í','L','N']
    perms_diacrit = generate_perms(letters_diacrit)

    # Výpis výsledkov
    print("Permutácie bez diakritiky (D, E, I, L, N):")
    for s in perms_ascii:
        print(s)

    print("\nPermutácie so zadanou diakritikou (D, E, Í, L, N):")
    for s in perms_diacrit:
        print(s)

    # Prípadne filtrovatie podľa slovníka (ak máš konkrétny slovník, vlož ho)
    # napríklad:
    # valid_ascii = [w for w in perms_ascii if is_word_in_dictionary(w, slovnik_slovenčina)]
    # print("\nPlatné slová (ascii):", valid_ascii)

if __name__ == "__main__":
    main()
    