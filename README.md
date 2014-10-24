What is AutoScale DynamoDB
====================

A PHP scripts which enable to automatic capacity scaling Amazon DynamoDB.

Amazon DynamoDBのスループットを自動的にスケーリングするためのPHPスクリプトです。

Similer project
---------------

* [Dynamic DynamoDB](http://dynamic-dynamodb.readthedocs.org/en/latest/)

How to use
----------

autoscale.ini を編集してください。
```php
[global]
AWS_ACCESS_KEY_ID = '******************'
AWS_SECRET_ACCESS_KEY = '*************************************'
AWS_REGION_NAME = 'ap-northeast-1'
;
; table "ip_geo"
[tables.ip_geo]
Read.Min = 300
Read.Max = 10000
Write.Min = 5
Write.Max = 7000
```
###起動方法

####ログっぽいものを出力する

/home/ec2-user/AutoScale/AutoScale_DynamoDB.php

####動作に問題が無ければ、こんな感じで動かしてください。

/home/ec2-user/AutoScale/AutoScale_DynamoDB.php < /dev/null > /dev/null &


####処理内容
5分ごとに、各テーブルに設定されたスループット値(ProvisionedCapacityUnit値)と
直近5分間における使用スループット値（ConsumedCapacityUnit値）とを比較して、
各テーブルのスループット値利用率を求めます。その上で、下記の条件でチェックを
行って必要な場合はProvisionedCapacityUnit値を変更します。


・利用率が80％を超えていたら、
　→ ProvisionedCapacityUnit値を50%増の値に変更する

・利用率が25％以下の状態が2時間（5分間隔×24回）継続していたら、
　→ ProvisionedCapacityUnit値を現在の使用スループット値×3の値に変更する

※ ProvisionedCapacityUnit値を変更する際には、各テーブル毎の最大値最小値を考慮してそれらを超えない値に調整します。

Case Study
-----------

* [slideshareに公開した資料] (http://www.slideshare.net/KenNakanishi/amazon-dynamodb-37348630)
* [AWS summit tokyo で講演した内容の録画](https://www.youtube.com/watch?v=EGZzRpWZEhY)

Requirements
------------

 * AWS SDK for PHP

Resources
-----------------------

### AWS SDK for PHP

* [AWS SDK for PHP](http://aws.amazon.com/jp/sdkforphp/)

### Amazon DynamoDB

* [Operations in Amazon DynamoDB](http://docs.amazonwebservices.com/amazondynamodb/latest/developerguide/operationlist.html)


