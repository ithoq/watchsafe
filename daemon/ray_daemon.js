var net = require('net');
var port = 9999;

var server = net.createServer(function(c) { //'connection' listener
  console.log('Client connected...');
  c.on('data', function(buf){
      console.log(buf);
   });
});

server.listen(port, function() { //'listening' listener
  console.log('Server waiting for connections on port '+port+' ...');
});





