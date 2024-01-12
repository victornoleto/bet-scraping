# Bet Scraping

## Instalação

Após clonar o projeto na sua máquina, instale as dependências: `composer install`

Copie o arquivo `.env.example` para `.env` e ajuste as configurações do banco de dados.

Execute o comando `php artisan migrate:fresh`

## Configurar supervisor

Basicamente há dois jobs na aplicação. Um para baixar as partidas e outro para baixar as odds.

As partidas só precisam ser baixadas uma vez. Já as odds podem ser atualizadas em um intervalo configurado no arquivo `.env`, `ODDS_REFRESH_MINUTES` (usar valor em minutos).

Para saber como instalar o supervisor, siga o tutorial: https://www.digitalocean.com/community/tutorials/how-to-install-and-manage-supervisor-on-ubuntu-and-debian-vps

### Criar arquivo para processar o download de partidas

Dentro de `/etc/supervisor/conf.d` crie um arquivo `bet-scraping-matches.conf`

```
[program:bet-scraping-matches.conf]
process_name=%(program_name)s_%(process_num)02d
command=php /diretorio/do/projeto/artisan queue:work --queue=matches
autostart=false
autorestart=false
stopasgroup=true
killasgroup=true
user=victor
numprocs=1
redirect_stderr=true
stdout_logfile=/diretorio/do/projeto/storage/logs/matches-process.log
stderr_logfile_maxbytes=10
stderr_logfile_backups=5
```

### Criar arquivo para processar o download de odds

Dentro de `/etc/supervisor/conf.d` crie um arquivo `bet-scraping-matches.conf`

```
[program:bet-scraping-odds.conf]
process_name=%(program_name)s_%(process_num)02d
command=php /diretorio/do/projeto/artisan queue:work --queue=odds
autostart=false
autorestart=false
stopasgroup=true
killasgroup=true
user=victor
numprocs=1
redirect_stderr=true
stdout_logfile=/diretorio/do/projeto/storage/logs/odds-process.log
stderr_logfile_maxbytes=10
stderr_logfile_backups=5
```

> No arquivo `bet-scraping-odds` você até pode aumentar o número de processos para processar a fila, mas não é recomendado. Isso porque quanto mais processos simultâneos mais requisições ao servidor serão feitas e isso pode acarretar no erro `429 Too Many Requests`. 

Atenção! Lembre de substituir `/diretorio/do/projeto` pelo caminho da raiz do seu projeto (onde o arquivo `artisan` está localizado).

Após criar os dois arquivos, reinicie o supervisor:

```
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart all
```

## Baixar partidas e odds

Utilize o comando `php artisan app:get-daily-matches {sport?} {date?} {--popular-league-only}` para baixar as partidas.

Automaticamente após baixar uma partida, as odds começaram a serem atualizadas. Após as odds da partida serem atualizadas, ela será adicionada na fila novamente após `ODDS_REFRESH_MINUTES` minutos. Isso vai acontecer enquanto não se passarem `STOP_UPDATE_ODDS_AFTER` minutos após o começo da partida.

> Exemplo: Para `ODDS_REFRESH_MINUTES=10` e `STOP_UPDATE_ODDS_AFTER=90` uma partida que começa às 12:00 terá as odds atualizadas de 10 em 10 minutos até às 13:30

A aplicação está configurada (`app/Console/Kernel.php`) para baixar as partidas de futebol e basquete (das ligas populares) do dia seguinte. Para isso funcionar corretamente, [lembre-se de configurar corretamente o agendador de processos](https://laravel.com/docs/10.x/scheduling#running-the-scheduler).

> Atenção! Após mudar uma configuração no arquivo `.env` lembre-se de reiniciar o supervisor!