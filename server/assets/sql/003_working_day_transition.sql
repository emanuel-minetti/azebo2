alter table working_day
    modify time_off enum (
        'urlaub',
        'gleitzeit',
        'ausgleich',
        'azv',
        'gruen',
        'gruenhalb',
        'zusatz',
        'krank',
        'kind',
        'da_krank',
        'da_befr',
        'reise',
        'befr',
        'sonder',
        'bildung_url',
        'bildung')
        null;
