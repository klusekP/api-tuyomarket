# Aplikacja TUYO

## Instalacja

Wszystkie pakiety zależne zarządzane są po stronie composera
```bash
composer install
```

Aplikacja uruchamiana jest jako 3(4) kontenery dockera

```bash
docker-compose up
```

* tuyo-mongo            - serwer MONGO DB 
* tuyo-mongo-express    - serwer aplikacji Mongo Express - interfejs web'owy do przeglądania zawartości bazy danych. 
                          W warunkach produkcyjnych prawdopodobnie usunąć ten kontener
* tuyo-php              - serwer aplikacji PHP
* tuyo-webserwer        - serwer NGINX

## Jason Web Token

W celu poprawnego działania uwierzytelniania, potrzebujemy dodatkowego zabiegu w postaci wygenerowania pary kluczy.

```bash
# Tworzymy katalog dla naszych kluczy
$ mkdir var/jwt                                             

# Tworzymy klucz prywatny. Wywołany zostanie prompt w którym podajemy dodatkowe hasło dla naszego klucza prywatnego.
$ openssl genrsa -out var/jwt/private.pem -aes256 4096      

# Tworzymy klucz publiczny dla naszego poprzednio wygenerowanego klucza prywatnego
$ openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem 
```

## Po uruchomieniu

Aplikacja będzie dostępna pod http://localhost:8100

Mongo express słucha pod http://localhost:8101

Dokumentacja API dostępna jest pod http://localhost:8100/api/doc

# Testy automatyczne

## Instalacja

Do automatycznych testów został wykorzystany behat. Przed pierwszym uruchomieniem należy wykonać inicjalizację

```bash
vendor/bin/behat --init
```

Następne wymagane jest uzupełnienie pliku ```behat.yml``` który powstał na podstawie ```behat.yml.dist``` odpowiednimi 
dla nas wartościami.

## Testowanie

Następnie testy uruchamianse są korzystając z komendy

```bash
vendor/bin/behat [plik scenariusza] [:linia w której znajduje się scenariusz]
```

Testy są napisane w Gherkin i znajdują się w plikach *.feature w katalogu ```features/api/```