# Passport control

This service is to check a batch of passport series and numbers against a list of expired and non-valid passports.

For more information see 
[http://сервисы.гувм.мвд.рф/info-service.htm?sid=2000](http://xn--b1afk4ade4e.xn--b1ab2a0a.xn--b1aew.xn--p1ai/info-service.htm?sid=2000).

## Usage

```shell
git clone https://github.com/maurokouti/passport-control.git
cd ./passport-control
docker-compose up -d
```

### Update a dataset
```shell
docker-compose exec app php bin/update.php
```

### Make a request

The service accepts HTTP requests with the body in CSV format: 
```shell
curl -iv http://127.0.0.1:8080/ --data-binary @- << END
1234,123456
5411,222110
4321,654321
3211,053785
END
```

### Response format

The service replies with the series and number found in the list.

```
5411,222110
3211,053785
```

`204 No Content` code is returned when nothing was found.

## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information.