var net = require('net');
var port = 9999; // Specify port here
//require('log-buffer');
//console.log(1);



var server = net.createServer(function(req, res) { //'connection' listener

  console.log('Client connected. Client ip - '+req.remoteAddress);
  req.on('data', function(buf){
                                        var command_id = generate_buf("41 54 24 53 41 56 45");
                                        
                                        req.write(command_id);
        });
});

server.listen(port, function() { //'listening' listener
  console.log('Server waiting for connections on port '+port+' ...');
});

function generate_buf(str)
{
 array = str.split(' ');
 if (array.length>0)
 {
 for (i=0;i<array.length;i++)
 {
 array[i]=base_convert(array[i], 16, 10);
 } 
 buf = new Buffer(array);
 return buf;
 }
 else
   return null;
}





