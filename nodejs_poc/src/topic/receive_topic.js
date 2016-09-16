#!/usr/bin/env node

var amqp = require('amqplib/callback_api');

var args = process.argv.slice(2);

if (args.length == 0) {
    console.log("Usage: receive_logs_topic.js <routing_key_>");
    process.exit(1);
}

amqp.connect('amqp://localhost', function(err, conn) {
    conn.createChannel(function(err, ch) {
        var ex = 'topic';
        var q = 'topic'
        ch.assertExchange(ex, 'topic', {durable: true});

        ch.assertQueue(q, {durable: true});
        console.log(' [*] Waiting for logs. To exit press CTRL+C');

        args.forEach(function(key) {
            ch.bindQueue(q, ex, key);
        });

        ch.consume(q, function(msg) {
            console.log(" [x] %s:'%s'", msg.fields.routingKey, msg.content.toString());
        }, {noAck: true});

    });
});