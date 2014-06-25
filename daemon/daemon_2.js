
var net = require('net');
var mysql = require('mysql');
var temp = require('redis');
var request = require('request');
var dateFormat = require('dateformat');
var port = 1338; // Specify port here
var redis = temp.createClient();
var sql = '';
var response = '';
var remote_ip;

 // Mysql DB cridentials here
var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : '724085',
  database : 'mattgps',
});

 connection.connect();




//-----------------------------------------------------Functions------------------------------------------------------------------------------------------------------///
function gen_array_for_mktime(str)
{
   console.log(str);
   var temp,temp1,temp2;
   temp = str.split(' ');
   temp1 = temp[0].split('-');
   temp2 = temp[1].split(':');

   var result = [ temp2[0], temp2[1], temp2[2], temp1[1], temp1[2], temp1[0] ];
   return result;
}
// Same as mktime in PHP date fromat example - 2013-05-06 08:58:38
function mktime() { // Get Unix timestamp for a date
  // 
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: baris ozdil

  var i = 0, d = new Date(), argv = arguments, argc = argv.length;

  var dateManip = {
    0: function(tt){ return d.setHours(tt); },
    1: function(tt){ return d.setMinutes(tt); },
    2: function(tt){ return d.setSeconds(tt); },
    3: function(tt){ return d.setMonth(parseInt(tt)-1); },
    4: function(tt){ return d.setDate(tt); },
    5: function(tt){ return d.setYear(tt); }
  };

  for( i = 0; i < argc; i++ ){
    if(argv[i] && isNaN(argv[i])){
      return false;
    } else if(argv[i]){
      // arg is number, let's manipulate date object
      if(!dateManip[i](argv[i])){
        // failed
        return false;
      }
    }
  }

  return Math.floor(d.getTime()/1000);
}

// Generate Buffer from string. String example "01 02 03". Only must be space separated values
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
// Convert Array to string
function implode( glue, pieces ) { 
  return ( ( pieces instanceof Array ) ? pieces.join ( glue ) : pieces );
}
// Generate string from Buffer
function generate_string_from_buf(buf)
{
  str='';
  for (i=0;i<buf.length;i++)
  {
       str+=base_convert(buf[i],10,16)+' ';
  }
  return str;
}
// Replace values in give string
function str_replace(haystack, needle, replacement) { 
  var temp = haystack.split(needle); 
  return temp.join(replacement); 
} 
// Simple base convert function
function simple_base_convert(number, frombase, tobase){
  res = parseInt(number, frombase).toString(tobase);
  return res;
 }
// Complex base convert function
function base_convert(number, frombase, tobase){
  res = parseInt(number, frombase).toString(tobase);

  if (tobase==2)
  {
     count=0;
      for(i=7;i>=0;i--)
      {
        if (typeof(res[i])=='undefined')
        {
          count++;
        }
      }
      for (i=count;i>0;i--)
      {
         res = '0'+res;
      }

      return res;
  }
  else
  {
     if (!res[1])
        {
          return (String)('0'+res);
          }
          else
             return res;
  }
 }
 // Make correct ODB_array
 function make_correct_odb(data)
 {
   temp_arr = new Array;

     temp_arr[0] = data[0];
     temp_arr[1] = data[1];
     temp_arr[2] = data[2];
     temp_arr[3] = data[3];
     temp_arr[4] = data[4];
     temp_arr[5] = data[5]+data[6];
     temp_arr[6] = data[7];
     temp_arr[7] = data[8];
     temp_arr[8] = data[9]+data[10];
     temp_arr[9] = data[11];
     temp_arr[10] = data[12]+data[13];
     temp_arr[11] = data[14]+data[15];
     temp_arr[12] = data[16];
     temp_arr[13] = data[17];
     temp_arr[14] = data[18]+data[19];
     temp_arr[15] = data[20];
     temp_arr[16] = data[21];
     temp_arr[17] = data[22]+data[23];
     temp_arr[18] = data[24]+data[25];
     
     for(var i in temp_arr)
     {
       if (typeof(temp_arr[i])=='undefined')
         {
           return false;
         }
     }

     return temp_arr;
 }
// Filter Buffer and split on correct strings
  function buf_filter(buf)
  {

      var result_array = []; 
      var temp = [];
      for(var i=0;i<buf.length;i++)
      { 
        temp.push(base_convert(buf[i],10,16));
          if ((base_convert(buf[i],10,16)=='0a') && (base_convert(buf[i-1],10,16)=='0d'))
          {
            result_array.push(temp);
            temp = [];
          }
      }

      return result_array;
  }

  function verify_odb_array_on_digits(temp_arr)
  {

       for  (var key in temp_arr)
           {
       
              if (temp_arr[key].length>2)
              {
                return false;
              }
              
           }
           return true;
  }
  
  function verify_odb_array_on_integrity(temp_arr)
  {

     for(var i in temp_arr)
     {
       if (typeof(temp_arr[i])=='undefined')
         {
           return false;
         }
     }
     return true;
  }
  // parse 161 message
  function parse_161_data(str)
  {
    var temp_arr = new Array;
    var data_arr = new Array;
    for(var i=0;i<str.length;i++)
       {
          temp_arr.push(str[i]);
       }
         data_arr['Modem_ID'] = temp_arr[0];
         data_arr['GPS_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[1]);
         data_arr['Longitude'] = temp_arr[2];
         data_arr['Latitude'] = temp_arr[3];
         data_arr['Speed'] = temp_arr[4];
         data_arr['Direction'] = temp_arr[5];
         data_arr['Altitude'] = temp_arr[6];
         data_arr['Satellites'] = temp_arr[7];
         data_arr['Message_ID'] = temp_arr[8];
         data_arr['Input_Status'] = temp_arr[9];
         data_arr['Output_Status'] = temp_arr[10];
         data_arr['Analog_Input1'] = temp_arr[11];
         data_arr['Analog_Input2'] = temp_arr[12];
         data_arr['RTC_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[13]);
         data_arr['Mileage'] = temp_arr[14];
         data_arr['geo_fence_index'] = temp_arr[15];
         return data_arr; 
  } 
   // Parse 11 message
  function  parse_11_data(str)
  {
    var temp_arr = new Array;
    var data_arr = new Array;
    for(var i=0;i<str.length;i++)
       {
          temp_arr.push(str[i]);
       }
         data_arr['Modem_ID'] = temp_arr[0];
         data_arr['GPS_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[1]);
         data_arr['Longitude'] = temp_arr[2];
         data_arr['Latitude'] = temp_arr[3];
         data_arr['Speed'] = temp_arr[4];
         data_arr['Direction'] = temp_arr[5];
         data_arr['Altitude'] = temp_arr[6];
         data_arr['Satellites'] = temp_arr[7];
         data_arr['Message_ID'] = temp_arr[8];
         data_arr['Input_Status'] = temp_arr[9];
         data_arr['Output_Status'] = temp_arr[10];
         data_arr['Analog_Input1'] = temp_arr[11];
         data_arr['Analog_Input2'] = temp_arr[12];
         data_arr['RTC_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[13]);
         data_arr['Mileage'] = temp_arr[14];
         return data_arr; 
  }
   // Parse 59 message
  function parse_59_data(str)
  {
    var temp_arr = new Array;
    var data_arr = new Array;
    for(var i=0;i<str.length;i++)
       {
          temp_arr.push(str[i]);
       }
         data_arr['Modem_ID'] = temp_arr[0];
         data_arr['GPS_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[1]);
         data_arr['Longitude'] = temp_arr[2];
         data_arr['Latitude'] = temp_arr[3];
         data_arr['Speed'] = temp_arr[4];
         data_arr['Direction'] = temp_arr[5];
         data_arr['Altitude'] = temp_arr[6];
         data_arr['Satellites'] = temp_arr[7];
         data_arr['Message_ID'] = temp_arr[8];
         data_arr['Input_Status'] = temp_arr[9];
         data_arr['Output_Status'] = temp_arr[10];
         data_arr['Analog_Input1'] = temp_arr[11];
         data_arr['Analog_Input2'] = temp_arr[12];
         data_arr['RTC_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[13]);
         data_arr['Mileage'] = temp_arr[14];
         data_arr['Text'] = temp_arr[15];
         return data_arr; 
  } 
   // Parse 323 message
  function parse_323_data(str)
  {
    var temp_arr = new Array;
    var data_arr = new Array;
    for(var i=0;i<str.length;i++)
       {
          temp_arr.push(str[i]);
       }
         data_arr['Modem_ID'] = temp_arr[0];
         data_arr['GPS_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[1]);
         data_arr['Longitude'] = temp_arr[2];
         data_arr['Latitude'] = temp_arr[3];
         data_arr['Speed'] = temp_arr[4];
         data_arr['Direction'] = temp_arr[5];
         data_arr['Altitude'] = temp_arr[6];
         data_arr['Satellites'] = temp_arr[7];
         data_arr['Message_ID'] = temp_arr[8];
         data_arr['Input_Status'] = temp_arr[9];
         data_arr['Output_Status'] = temp_arr[10];
         data_arr['Analog_Input1'] = temp_arr[11];
         data_arr['Analog_Input2'] = temp_arr[12];
         data_arr['RTC_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[13]);
         data_arr['Mileage'] = temp_arr[14];
         data_arr['Over_STEP'] = (temp_arr[15]*100/255).toFixed(2);
         return data_arr; 
  } 
  // Parse 322 message
  function parse_322_data(str)
  {
    var temp_arr = new Array;
    var data_arr = new Array;
    for(var i=0;i<str.length;i++)
       {
          temp_arr.push(str[i]);
       }
         data_arr['Modem_ID'] = temp_arr[0];
         data_arr['GPS_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[1]);
         data_arr['Longitude'] = temp_arr[2];
         data_arr['Latitude'] = temp_arr[3];
         data_arr['Speed'] = temp_arr[4];
         data_arr['Direction'] = temp_arr[5];
         data_arr['Altitude'] = temp_arr[6];
         data_arr['Satellites'] = temp_arr[7];
         data_arr['Message_ID'] = temp_arr[8];
         data_arr['Input_Status'] = temp_arr[9];
         data_arr['Output_Status'] = temp_arr[10];
         data_arr['Analog_Input1'] = temp_arr[11];
         data_arr['Analog_Input2'] = temp_arr[12];
         data_arr['RTC_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[13]);
         data_arr['Mileage'] = temp_arr[14];
         data_arr['Over_TEMP'] = temp_arr[15] - 40;
         return data_arr; 
  } 
// Parse 321 message
  function parse_321_data(str)
  {
    var temp_arr = new Array;
    var data_arr = new Array;
    for(var i=0;i<str.length;i++)
       {
          temp_arr.push(str[i]);
       }
         data_arr['Modem_ID'] = temp_arr[0];
         data_arr['GPS_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[1]);
         data_arr['Longitude'] = temp_arr[2];
         data_arr['Latitude'] = temp_arr[3];
         data_arr['Speed'] = temp_arr[4];
         data_arr['Direction'] = temp_arr[5];
         data_arr['Altitude'] = temp_arr[6];
         data_arr['Satellites'] = temp_arr[7];
         data_arr['Message_ID'] = temp_arr[8];
         data_arr['Input_Status'] = temp_arr[9];
         data_arr['Output_Status'] = temp_arr[10];
         data_arr['Analog_Input1'] = temp_arr[11];
         data_arr['Analog_Input2'] = temp_arr[12];
         data_arr['RTC_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[13]);
         data_arr['Mileage'] = temp_arr[14];
         data_arr['Over_RPM'] = (temp_arr[15]/4).toFixed(2);
         return data_arr; 
  }  
// Parse 180 message
 function parse_180_data(str)
 {
    var temp_arr = new Array;
    var data_arr = new Array;
    for(var i=0;i<str.length;i++)
       {
          temp_arr.push(str[i]);
       }
         data_arr['Modem_ID'] = temp_arr[0];
         data_arr['GPS_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[1]);
         data_arr['Longitude'] = temp_arr[2];
         data_arr['Latitude'] = temp_arr[3];
         data_arr['Speed'] = temp_arr[4];
         data_arr['Direction'] = temp_arr[5];
         data_arr['Altitude'] = temp_arr[6];
         data_arr['Satellites'] = temp_arr[7];
         data_arr['Message_ID'] = temp_arr[8];
         data_arr['Input_Status'] = temp_arr[9];
         data_arr['Output_Status'] = temp_arr[10];
         data_arr['Analog_Input1'] = temp_arr[11];
         data_arr['Analog_Input2'] = temp_arr[12];
         data_arr['RTC_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[13]);
         data_arr['Mileage'] = temp_arr[14];
         data_arr['X_Axis'] = temp_arr[15];
         data_arr['Y_Axis'] = temp_arr[16];
         data_arr['Z_Axis'] = temp_arr[17];
         return data_arr; 
 }
// Parse 160 message
 function parse_160_data(str)
 {
    var temp_arr = new Array;
    var data_arr = new Array;
    for(var i=0;i<str.length;i++)
       {
          temp_arr.push(str[i]);
       }
         data_arr['Modem_ID'] = temp_arr[0];
         data_arr['GPS_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[1]);
         data_arr['Longitude'] = temp_arr[2];
         data_arr['Latitude'] = temp_arr[3];
         data_arr['Speed'] = temp_arr[4];
         data_arr['Direction'] = temp_arr[5];
         data_arr['Altitude'] = temp_arr[6];
         data_arr['Satellites'] = temp_arr[7];
         data_arr['Message_ID'] = temp_arr[8];
         data_arr['Input_Status'] = temp_arr[9];
         data_arr['Output_Status'] = temp_arr[10];
         data_arr['Analog_Input1'] = temp_arr[11];
         data_arr['Analog_Input2'] = temp_arr[12];
         data_arr['RTC_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[13]);
         data_arr['Mileage'] = temp_arr[14];
         data_arr['Main_Battery'] = temp_arr[15];
         data_arr['Back_Up_Battery'] = temp_arr[16];
         return data_arr;  
 }
 // Parse 163 message
 function parse_163_data(str)
 {
    var temp_arr = new Array;
    var data_arr = new Array;
    for(var i=0;i<str.length;i++)
       {
          temp_arr.push(str[i]);
       }
         data_arr['Modem_ID'] = temp_arr[0];
         data_arr['GPS_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[1]);
         data_arr['Longitude'] = temp_arr[2];
         data_arr['Latitude'] = temp_arr[3];
         data_arr['Speed'] = temp_arr[4];
         data_arr['Direction'] = temp_arr[5];
         data_arr['Altitude'] = temp_arr[6];
         data_arr['Satellites'] = temp_arr[7];
         data_arr['Message_ID'] = temp_arr[8];
         data_arr['Input_Status'] = temp_arr[9];
         data_arr['Output_Status'] = temp_arr[10];
         data_arr['Analog_Input1'] = temp_arr[11];
         data_arr['Analog_Input2'] = temp_arr[12];
         data_arr['RTC_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[13]);
         data_arr['Mileage'] = temp_arr[14];
         data_arr['MAX_Speed'] = (temp_arr[15]*3.6).toFixed(2);
         data_arr['AVG_Speed'] = (temp_arr[16]*3.6).toFixed(2);
         data_arr['MAX_Speed_Duration'] = temp_arr[17];
         return data_arr;  
 }

 // Get message type
 function getMessageID(str)
 {
     for(var i=0;i<str.length;i++)
       {
          if (i==8) return str[i];
       }
 }
// Parse GPS_data array 
 function parse_GPSdata(temp_arr)
 {
         var data_arr = new Array;
         data_arr['Modem_ID'] = temp_arr[0];
         data_arr['GPS_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[1]);
         data_arr['Longitude'] = temp_arr[2];
         data_arr['Latitude'] = temp_arr[3];
         data_arr['Speed'] = temp_arr[4];
         data_arr['Direction'] = temp_arr[5];
         data_arr['Altitude'] = temp_arr[6];
         data_arr['Satellites'] = temp_arr[7];
         data_arr['Message_ID'] = temp_arr[8];
         data_arr['Input_Status'] = temp_arr[9];
         data_arr['Output_Status'] = temp_arr[10];
         data_arr['Analog_Input1'] = temp_arr[11];
         data_arr['Analog_Input2'] = temp_arr[12];
         data_arr['RTC_DataTime'] = convert_gpsDataTime_to_timestamp(temp_arr[13]);
         data_arr['Mileage'] = temp_arr[14];

         send_coordinates_to_kohana(temp_arr[0], temp_arr[3], temp_arr[2], temp_arr[4]);
         return data_arr;
 }
 // Parse Imei
 function parse_imei(temp_arr)
 {
    if ((temp_arr[0].length)===15)
    {
      return temp_arr[0];
    }
    else
    {
      return false; 
    }
 }
 // Parse ODB_data array 
 function parse_ODBdata(temp_arr)
 {
         var data_arr = new Array;
         data_arr['(MIL)status'] =  (base_convert(temp_arr[0],16,2)[0]==1) ? 'ON' : 'OFF';
         if (data_arr['(MIL)status']=='ON') 
         {
          var binary_data = base_convert(temp_arr[0],16,2);
              binary_data = binary_data[1]+binary_data[2]+binary_data[3]+binary_data[4]+binary_data[5]+binary_data[6]+binary_data[7]; 
    
          data_arr['number_of_DTC_codes'] =  base_convert(binary_data,2,10);
         }
         else
          {
            data_arr['number_of_DTC_codes'] = null;
          } 
         data_arr['Engine load'] = (simple_base_convert(temp_arr[1],16,10)*100/255).toFixed(2);
         data_arr['Engine coolant'] = Number(simple_base_convert(temp_arr[2],16,10)-40);
         data_arr['Fuel pressure'] = Number(simple_base_convert(temp_arr[3],16,10)*3);
         data_arr['Intake manifold pressure'] = Number(simple_base_convert(temp_arr[4],16,10));
         data_arr['Engine RPM'] = (simple_base_convert(temp_arr[5],16,10)/4).toFixed(2);
         data_arr['ODB Speed'] = Number(simple_base_convert(temp_arr[6],16,10));
         data_arr['Intake temp'] = Number(simple_base_convert(temp_arr[7],16,10));
         data_arr['MAF air flow rate'] = (Number(simple_base_convert(temp_arr[8],16,10))/100).toFixed(2);  
         data_arr['Throttle position'] = (simple_base_convert(temp_arr[9],16,10)*100/255).toFixed(2);
         data_arr['Run time since engine start'] = Number(simple_base_convert(temp_arr[10],16,10));
         data_arr['Distance travelled with error'] = Number(simple_base_convert(temp_arr[11],16,10));
         data_arr['Fuel level'] = (Number(simple_base_convert(temp_arr[12],16,10))*100/255).toFixed(2);
         data_arr['Barometric pressure'] = Number(simple_base_convert(temp_arr[13],16,10));
         data_arr['Control module voltage'] = (Number(simple_base_convert(temp_arr[14],16,10))/1000).toFixed(2);
         data_arr['Air temp'] = (Number(simple_base_convert(temp_arr[15],16,10))!=0) ? Number(simple_base_convert(temp_arr[15],16,10))-40 : 0;
         data_arr['Accel pedal pos'] = (Number(simple_base_convert(temp_arr[16],16,10))*100/255).toFixed(2);
         data_arr['Total fuel used'] = (Number(simple_base_convert(temp_arr[17],16,10))*0.1).toFixed(2);
         data_arr['OBD Odometer'] = Number(simple_base_convert(temp_arr[18],16,10));
         return data_arr;
 }
// function for convert server date to timestamp
function convert_gpsDataTime_to_timestamp(date)
{
  var year = date.substr(0,4),
      month = date.substr(4,2),
      day = date.substr(6,2),
      hours = date.substr(8,2),
      min = date.substr(10,2),
      sec = date.substr(12,2);
      return year+'-'+month+'-'+day+' '+hours+':'+min+':'+sec;
}

function send_coordinates_to_kohana(imei, lat, lon, speed)
{
   var options = {
      url: 'http://54.206.51.204/ajax/sendCoordinates?imei='+imei+'&lat='+lat+'&lon='+lon+'&speed='+speed
    };


  request(options,function (error, response, body) {
    if (!error && response.statusCode == 200) {
       console.log(options);
    }
  });

}

function send_data_to_kohana(data)
{
   var options = {
      url: 'http://54.206.51.204/ajax/sendEmail'+data
    };


  request(options,function (error, response, body) {
    if (!error && response.statusCode == 200) {
       console.log('Email was sent');
       console.log('Data - '+data);
    }
  });

}


//------------------------------------------------------End Functions-------------------------------------------------------------------------------------------------///
//var counter = 0;
var server = net.createServer(function(req, res) { //'connection' listener

  console.log('Client connected. Client ip - '+req.remoteAddress);
  req.on('data', function(buf){
         //
          //str = "33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 31 30 35 30 32 33 38 30 32 2c 31 35 31 2e 32 31 39 37 32 36 2c 2d 33 33 2e 39 31 31 33 31 2c 30 2c 30 2c 30 2c 30 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 30 34 38 2c 34 2e 30 34 38 2c 32 30 31 33 30 31 30 35 30 32 33 38 30 32 2c 34 32 32 38 32 38 2c 30 30 2c 34 62 2c 38 33 2c 30 30 2c 32 39 2c 33 31 2c 35 34 2c 33 30 2c 30 31 2c 32 36 2c 30 30 2c 30 30 2c 30 30 2c 30 30 2c 30 30 2c 30 30 2c 30 30 2c 30 30 2c 30 30 2c 30 30 2c 65 32 2c 30 30 2c 64 32 2c 30 31 2c 30 30 2c 30 30 0d 0a";
         //From email
         // str = "31 30 30 32 31 30 37 30 30 31 2c 32 30 31 32 31 31 30 38 30 38 32 34 32 38 2c 31 32 31 2e 36 34 36 31 31 30 2c 32 35 2e 30 36 32 37 30 32 2c 30 2c 32 31 33 2c 33 32 2c 35 2c 33 30 30 2c 31 2c 30 2c 31 31 2e 36 38 30 2c 30 2e 30 30 30 2c 32 30 31 32 31 31 30 38 30 38 32 34 32 38 2c 31 32 35 31 35 38 35 32 34 32 2c 30 30 2c 30 30 2c 61 34 2c 30 30 2c 30 30 2c 35 66 65 38 2c 63 62 2c 30 30 2c 31 32 63 30 2c 30 30 2c 30 30 37 30 2c 30 30 30 30 2c 30 30 2c 30 30 2c 30 30 30 30 2c 30 30 2c 30 30 2c 30 30 38 30 2c 30 30 31 37 2c 30 30 2c 30 30 0d 0a";
         // Broken string      
         //str = "2c 34 35 2c 36 39 2c 34 35 2c 30 30 2c 30 30 2c 34 30 2c 61 33 2c 31 30 2c 30 30 2c 30 30 2c 38 34 2c 30 30 2c 62 66 2c 33 31 2c 30 30 2c 35 66 2c 30 30 2c 30 30 2c 38 38 2c 30 31 2c 30 30 2c 30 30 0d 0a";
         // Long Broken string
         //str = "33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 31 30 34 31 31 30 31 35 35 2c 31 35 31 2e 32 31 35 33 36 32 2c 2d 33 33 2e 38 39 39 39 30 2c 35 33 2c 31 39 31 2c 36 33 2c 38 2c 33 30 30 2c 31 2c 30 2c 31 33 2e 38 34 30 2c 34 2e 30 36 34 2c 32 30 31 33 30 31 30 34 31 31 30 31 35 35 2c 34 32 31 33 34 38 2c 30 30 2c 33 62 2c 38 62 2c 30 30 2c 31 37 2c 30 38 2c 34 33 2c 30 30 2c 30 30 2c 31 66 2c 34 34 2c 30 33 2c 30 30 2c 30 30 2c 36 30 2c 30 30 2c 36 39 2c 33 36 2c 30 30 2c 33 31 2c 30 30 2c 30 30 2c 64 30 2c 30 31 2c 30 30 2c 30 30 0d 0a 33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 31 30 34 31 31 30 32 30 30 2c 31 35 31 2e 32 31 35 31 39 34 2c 2d 33 33 2e 39 30 30 33 36 2c 33 36 2c 31 39 35 2c 37 31 2c 37 2c 33 30 30 2c 31 2c 30 2c 31 33 2e 38 35 36 2c 34 2e 30 36 34 2c 32 30 31 33 30 31 30 34 31 31 30 32 30 30 2c 34 32 31 34 30 32 2c 30 30 2c 34 65 2c 38 62 2c 30 30 2c 31 65 2c 32 30 2c 34 33 2c 30 30 2c 30 30 2c 32 32 2c 34 38 2c 30 33 2c 30 30 2c 30 30 2c 36 62 2c 30 30 2c 39 30 2c 33 36 2c 30 30 2c 33 31 2c 30 30 2c 30 30 2c 64 30 2c 30 31 2c 30 30 2c 30 30 0d 0a 33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 31 30 34 31 31 30 32 30 35 2c 31 35 31 2e 32 31 35 31 30 33 2c 2d 33 33 2e 39 30 30 35 33 2c 35 2c 31 39 31 2c 36 36 2c 38 2c 33 30 30 2c 31 2c 30 2c 31 33 2e 38 34 30 2c 34 2e 30 36 34 2c 32 30 31 33 30 31 30 34 31 31 30 32 30 35 2c 34 32 31 34 32 33 2c 30 30 2c 36 39 2c 38 39 2c 30 30 2c 32 61 2c 39 38 2c 34 33 2c 30 30 2c 30 30 2c 32 31 2c 34 65 2c 30 33 2c 30 30 2c 30 30 2c 36 35 2c 30 30 2c 31 61 2c 33 36 2c 30 30 2c 33 31 2c 30 30 2c 30 30 2c 64 30 2c 30 31 2c 30 30 2c 30 30 0d 0a 33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 31 30 34 31 31 30 32 31 30 2c 31 35 31 2e 32 31 35 31 30 33 2c 2d 33 33 2e 39 30 30 35 34 2c 30 2c 31 39 31 2c 36 36 2c 38 2c 33 30 30 2c 31 2c 30 2c 31 33 2e 38 32 34 2c 34 2e 30 36 34 2c 32 30 31 33 30 31 30 34 31 31 30 32 31 30 2c 34 32 31 34 32 33 2c 30 30 2c 36 35 2c 38 39 2c 30 30 2c 32 38 2c 34 38 2c 30 30 2c 34 33 2c 30 30 2c 30 30 2c 32 31 2c 35 33 2c 30 33 2c 30 30 2c 30 30 2c 36 62 2c 30 30 2c 39 30 2c 33 36 2c 30 30 2c 33 31 2c 30 30 2c 30 30 2c 64 30 2c 30 31 2c 30 30 2c 30 30 0d 0a 33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 31 30 34 31 31 30 32 31 35 2c 31 35 31 2e 32 31 35 31 30 33 2c 2d 33 33 2e 39 30 30 35 34 2c 30 2c 31 39 31 2c 36 36 2c 38 2c 33 30 30 2c 31 2c 30 2c 31 33 2e 38 32 34 2c 34 2e 30 36 34 2c 32 30 31 33 30 31 30 34 31 31 30 32 31 35 2c 34 32 31 34 32 33 2c 30 30 2c 36 35 2c 38 39 2c 30 30 2c 32 38 2c 34 34 2c 30 30 2c 34 33 2c 30 30 2c 30 30 2c 32 31 2c 35 36 2c 30 33 2c 30 30 2c 30 30 2c 36 62 2c 30 30 2c 32 61 2c 33 36 2c 30 30 2c 33 31 2c 30 30 2c 30 30 2c 64 30 2c 30 31 2c 30 30 2c 30 30 0d 0a 33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 31 30 34 31 31 30 32 32 30 2c 31 35 31 2e 32 31 35 31 30 33 2c 2d 33 33 2e 39 30 30 35 34 2c 30 2c 31 39 31 2c 36 36 2c 37 2c 33 30 30 2c 31 2c 30 2c 31 33 2e 38 37 32 2c 34 2e 30 36 34 2c 32 30 31 33 30 31 30";
         //str = "45 52 52 4f 52 3a 55 4e 4b 4e 4f 57 4e 0d 0a";
         //str="33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 31 31 32 30 39 30 35 34 31 2c 31 35 31 2e 32 31 32 30 35 31 2c 2d 33 33 2e 38 32 35 30 34 2c 37 38 2c 31 32 36 2c 37 33 2c 38 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 32 35 36 2c 34 2e 30 34 38 2c 32 30 31 33 30 31 31 32 30 39 30 35 34 31 2c 34 39 36 35 2c 30 30 2c 34 34 2c 37 66 2c 30 30 2c 32 33 2c 33 34 30 63 2c 34 65 2c 34 38 2c 30 33 36 32 2c 32 64 2c 30 30 30 30 2c 30 30 30 30 2c 30 30 2c 30 30 2c 30 30 30 30 2c 30 30 2c 30 30 2c 30 30 30 33 2c 30 30 30 34 2c 30 30 2c 30 30 0d 0a";
          //str = '33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 31 31 37 31 34 35 32 30 38 2c 31 35 31 2e 32 31 39 36 35 30 2c 2d 33 33 2e 39 31 31 33 33 2c 30 2c 31 31 35 2c 34 35 2c 38 2c 32 2c 30 2c 30 2c 31 32 2e 34 30 30 2c 34 2e 30 36 34 2c 32 30 31 33 30 31 31 37 31 34 35 32 30 38 2c 31 34 37 33 0d 0a';         
         //str = '33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 32 31 37 30 36 30 30 35 36 2c 31 35 31 2e 31 37 39 30 30 30 2c 2d 33 33 2e 38 30 38 34 32 2c 39 2c 32 35 35 2c 31 30 35 2c 30 2c 31 37 30 2c 30 2c 30 2c 31 32 2e 30 36 34 2c 34 2e 31 32 38 2c 32 30 31 33 30 32 31 37 30 36 30 30 35 34 2c 31 34 35 30 2c 30 2c 34 31 33 33 0d 0a';
         //str='33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 32 31 37 30 37 34 33 31 38 2c 31 35 31 2e 31 37 38 36 33 34 2c 2d 33 33 2e 38 30 37 37 31 2c 31 32 30 2c 30 2c 30 2c 31 32 2c 31 36 32 2c 30 2c 30 2c 31 32 2e 30 33 32 2c 34 2e 31 31 31 2c 32 30 31 33 30 32 31 37 30 37 34 33 31 38 2c 31 34 35 30 0d 0a';
         //str ='33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 32 31 37 30 37 34 33 31 38 2c 31 35 31 2e 31 37 38 36 33 34 2c 2d 33 33 2e 38 30 37 37 31 2c 31 32 30 2c 30 2c 30 2c 31 32 2c 31 36 33 2c 30 2c 30 2c 31 32 2e 30 33 32 2c 34 2e 31 31 31 2c 32 30 31 33 30 32 31 37 30 37 34 33 31 38 2c 31 34 35 30 2c 34 30 2c 33 35 2c 31 30 0d 0a';
         //str = '33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 32 31 37 30 37 34 33 31 38 2c 31 35 31 2e 31 37 38 36 33 34 2c 2d 33 33 2e 38 30 37 37 31 2c 30 2c 30 2c 30 2c 30 2c 31 37 32 2c 30 2c 30 2c 31 32 2e 30 33 32 2c 34 2e 31 31 31 2c 32 30 31 33 30 32 31 37 30 37 34 33 31 38 2c 31 34 35 30 0d 0a';
         //str = '33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 32 31 37 30 37 34 33 31 38 2c 31 35 31 2e 31 37 38 36 33 34 2c 2d 33 33 2e 38 30 37 37 31 2c 30 2c 30 2c 30 2c 30 2c 31 37 38 2c 30 2c 30 2c 31 32 2e 30 33 32 2c 34 2e 31 31 31 2c 32 30 31 33 30 32 31 37 30 37 34 33 31 38 2c 31 34 35 30 0d 0a';
         //str = '33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 32 31 33 30 38 33 34 33 37 2c 31 35 31 2e 31 37 35 30 36 34 2c 2d 33 33 2e 38 30 38 36 32 2c 30 2c 32 32 32 2c 38 36 2c 38 2c 31 38 30 2c 30 2c 30 2c 31 33 2e 33 32 38 2c 34 2e 31 34 34 2c 32 30 31 33 30 32 31 33 30 38 33 34 33 37 2c 34 33 39 37 2c 30 2c 37 2c 2d 31 0d 0a';
         //str='33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 33 32 38 31 30 30 30 35 35 2c 31 35 31 2e 31 38 30 39 39 39 2c 2d 33 33 2e 38 30 35 31 31 2c 33 33 2c 31 34 30 2c 31 31 33 2c 36 2c 33 32 31 2c 31 2c 30 2c 31 34 2e 32 32 34 2c 34 2e 31 39 32 2c 32 30 31 33 30 33 32 38 31 30 30 30 35 35 2c 31 32 37 36 33 2c 31 31 38 34 34 0d 0a';
         //str='33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 33 32 34 30 30 30 37 33 34 2c 31 35 31 2e 31 37 38 30 30 39 2c 2d 33 33 2e 38 30 37 38 38 2c 33 39 2c 33 30 35 2c 38 37 2c 30 2c 33 32 33 2c 31 2c 30 2c 31 34 2e 34 30 30 2c 34 2e 31 32 38 2c 32 30 31 33 30 33 32 34 30 30 30 37 33 35 2c 30 2c 38 35 0d 0a';
         //str = '33 35 31 38 30 32 30 35 34 32 34 30 32 33 33 2C 32 30 31 33 30 33 32 39 30 32 30 33 35 35 2C 31 32 31 2E 36 34 36 31 31 30 2C 32 35 2E 30 36 32 38 34 39 2C 30 2C 31 37 39 2C 38 32 2C 38 2C 36 31 2C 30 2C 30 2C 31 30 2E 38 38 30 2C 34 2E 30 39 36 2C 32 30 31 33 30 33 32 39 30 32 30 33 35 35 2C 33 32 30 38 38 2C 4F 4B 3A 55 43 76 31 2E 30 72 30 34 2E 62 69 6E 0D 0A';
         //str = '33 35 31 38 30 32 30 35 34 32 34 30 32 33 33 2c 32 30 31 33 30 33 32 39 30 32 30 32 30 37 2c 31 32 31 2e 36 34 36 31 31 30 2c 32 35 2e 30 36 32 38 34 39 2c 30 2c 31 37 39 2c 38 32 2c 37 2c 36 30 2c 30 2c 30 2c 31 30 2e 38 39 36 2c 34 2e 30 36 34 2c 32 30 31 33 30 33 32 39 30 32 30 32 30 37 2c 33 32 30 38 38 2c 45 52 52 4f 52 3a 55 43 76 31 2e 30 72 30 34 2e 62 69 6e 0D 0A';
         //320
         //str='33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 33 33 31 31 33 35 37 31 32 2c 31 35 31 2e 31 34 37 36 37 34 2c 2d 33 33 2e 38 30 32 35 30 2c 31 36 2c 31 31 37 2c 33 37 2c 30 2c 33 32 30 2c 30 2c 30 2c 31 32 2e 38 33 32 2c 34 2e 31 34 34 2c 32 30 31 33 30 33 33 31 31 33 35 37 31 32 2c 31 34 35 32 34 2c 33 0d 0a';
         //str = "33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 34 30 33 30 38 31 37 32 38 2c 31 35 30 2e 39 39 32 39 31 39 2c 2d 33 33 2e 38 32 36 34 36 2c 38 38 2c 31 31 38 2c 34 32 2c 38 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 31 32 38 2c 34 2e 31 32 38 2c 32 30 31 33 30 34 30 33 30 38 31 37 32 38 2c 33 37 39 39 2c 30 33 2c 35 35 2c 38 31 2c 30 30 2c 30 30 2c 31 65 38 62 2c 35 35 2c 34 30 2c 30 37 65 39 2c 32 66 2c 30 30 30 30 2c 30 30 32 30 2c 30 30 2c 30 30 2c 30 30 30 30 2c 30 30 2c 30 30 2c 30 30 30 35 2c 30 30 30 33 2c 30 30 2c 30 30 0d 0a";
         //str = "4f 4b 3a 4f 42 44 47 44 54 43 0d 0a";
         //str="4f 4b 3a 4f 42 44 47 44 54 43 0d 0a 24 4f 42 44 47 44 54 43 3d 33 2c 30 31 32 32 2c 30 32 32 32 2c 31 36 33 32 0d 0a";
          //str='4f 4b 3a 48 4f 53 54 53 0d 0a 24 48 4f 53 54 53 3d 31 2c 30 2c 34 36 2e 31 37 35 2e 31 36 31 2e 31 31 34 2c 31 33 33 38 0d 0a 24 48 4f 53 54 53 3d 32 2c 30 2c 2c 30 0d 0a 24 48 4f 53 54 53 3d 33 2c 30 2c 2c 30 0d 0a 24 48 4f 53 54 53 3d 34 2c 30 2c 2c 30 0d 0a 24 48 4f 53 54 53 3d 35 2c 30 2c 2c 30 0d 0a 24 48 4f 53 54 53 3d 36 2c 30 2c 2c 30 0d 0a 24 48 4f 53 54 53 3d 37 2c 30 2c 2c 30 0d 0a 24 48 4f 53 54 53 3d 38 2c 30 2c 2c 30 0d 0a 24 48 4f 53 54 53 3d 39 2c 30 2c 2c 30 0d 0a 24 48 4f 53 54 53 3d 31 30 2c 30 2c 2c 30 0d 0a 48 43 3d 31 0d 0a';
         //str="33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 35 30 31 31 38 34 37 34 39 2c 31 35 31 2e 30 36 35 34 39 30 2c 2d 33 33 2e 38 39 38 35 32 2c 38 30 2c 31 39 30 2c 32 31 2c 38 2c 31 31 2c 31 2c 30 2c 31 34 2e 31 37 36 2c 34 2e 31 37 36 2c 32 30 31 33 30 35 30 31 31 38 34 37 34 39 2c 30 0d 0a 33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 35 30 31 31 38 33 39 35 33 2c 31 35 31 2e 31 30 31 34 35 35 2c 2d 33 33 2e 38 31 37 38 35 2c 30 2c 32 32 36 2c 34 39 2c 39 2c 32 30 36 2c 31 2c 30 2c 31 34 2e 31 39 32 2c 34 2e 31 37 36 2c 32 30 31 33 30 35 30 31 31 38 33 39 35 35 2c 31 30 36 35 0d 0a 33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 35 30 32 30 35 33 39 33 36 2c 31 35 30 2e 35 39 36 30 38 34 2c 2d 33 34 2e 38 37 31 35 32 2c 33 30 2c 39 34 2c 33 36 2c 35 2c 32 30 38 2c 31 2c 30 2c 31 34 2e 33 32 30 2c 34 2e 31 36 30 2c 32 30 31 33 30 35 30 32 30 35 33 39 33 38 2c 34 35 37 0d 0a 33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 35 30 31 31 39 34 30 30 35 2c 31 35 30 2e 38 36 38 35 33 30 2c 2d 33 34 2e 33 39 39 33 34 2c 38 31 2c 31 37 38 2c 31 32 34 2c 39 2c 31 31 2c 30 2c 30 2c 31 34 2e 31 31 32 2c 34 2e 31 37 36 2c 32 30 31 33 30 35 30 31 31 39 34 30 30 35 2c 36 39 36 36 34 0d 0a";
         //str = '33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 38 34 39 2c 31 35 31 2e 31 39 36 31 33 36 2c 2d 33 33 2e 38 32 34 31 38 2c 30 2c 31 37 2c 37 32 2c 30 2c 31 31 2c 31 2c 30 2c 31 34 2e 31 37 36 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 38 34 33 2c 30 0d 0a ';
         //str = '33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 38 34 39 2c 31 35 31 2e 31 39 36 31 33 36 2c 2d 33 33 2e 38 32 34 31 38 2c 30 2c 31 37 2c 37 32 2c 30 2c 31 31 2c 31 2c 30 2c 31 34 2e 31 37 36 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 38 34 33 2c 30 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 38 34 39 2c 31 35 31 2e 31 39 36 31 33 36 2c 2d 33 33 2e 38 32 34 31 38 2c 30 2c 31 37 2c 37 32 2c 30 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 31 37 36 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 38 34 33 2c 30 2c 30 30 2c 37 35 2c 30 30 2c 30 30 2c 32 64 2c 31 33 34 63 2c 30 36 2c 30 30 2c 30 30 30 30 2c 32 39 2c 30 30 30 36 2c 30 30 30 30 2c 30 30 2c 30 30 2c 30 30 30 30 2c 30 30 2c 33 62 2c 30 30 30 30 2c 30 30 30 33 2c 30 30 2c 30 30 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 38 34 39 2c 31 35 31 2e 31 39 36 31 33 36 2c 2d 33 33 2e 38 32 34 31 38 2c 30 2c 31 37 2c 37 32 2c 30 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 31 37 36 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 38 34 33 2c 30 2c 30 30 2c 37 35 2c 30 30 2c 30 30 2c 32 64 2c 31 33 34 63 2c 30 36 2c 30 30 2c 30 30 30 30 2c 32 39 2c 30 30 30 36 2c 30 30 30 30 2c 30 30 2c 30 30 2c 30 30 30 30 2c 30 30 2c 33 62 2c 30 30 30 30 2c 30 30 30 33 2c 30 30 2c 30 30 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 38 35 37 2c 31 35 31 2e 31 39 36 36 35 35 2c 2d 33 33 2e 38 32 34 35 31 2c 33 36 2c 31 34 37 2c 36 38 2c 35 2c 31 36 32 2c 31 2c 30 2c 31 34 2e 32 38 38 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 38 35 31 2c 36 31 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 38 35 37 2c 31 35 31 2e 31 39 36 36 35 35 2c 2d 33 33 2e 38 32 34 35 31 2c 33 36 2c 31 34 37 2c 36 38 2c 35 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 32 38 38 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 38 35 31 2c 36 31 2c 30 30 2c 64 63 2c 36 32 2c 30 30 2c 35 35 2c 31 64 36 34 2c 32 34 2c 35 38 2c 30 30 30 30 2c 35 37 2c 30 30 31 30 2c 30 30 30 30 2c 30 30 2c 36 34 2c 33 37 63 32 2c 34 35 2c 35 32 2c 30 30 30 30 2c 30 30 30 30 2c 30 30 2c 30 30 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 38 35 38 2c 31 35 31 2e 31 39 36 36 35 35 2c 2d 33 33 2e 38 32 34 35 31 2c 33 36 2c 31 34 37 2c 36 38 2c 35 2c 33 32 33 2c 31 2c 30 2c 31 34 2e 32 38 38 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 38 35 38 2c 36 31 2c 38 32 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 39 31 33 2c 31 35 31 2e 31 39 37 34 37 39 2c 2d 33 33 2e 38 32 35 35 30 2c 31 38 2c 31 32 30 2c 39 30 2c 38 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 32 38 38 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 39 31 33 2c 32 31 35 2c 30 30 2c 66 63 2c 36 38 2c 30 30 2c 36 31 2c 32 34 62 63 2c 31 63 2c 35 35 2c 30 30 30 30 2c 36 30 2c 30 30 31 64 2c 30 30 30 30 2c 30 30 2c 36 34 2c 33 37 63 32 2c 34 35 2c 35 33 2c 30 30 30 30 2c 30 30 30 30 2c 30 30 2c 30 30 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 39 31 33 2c 31 35 31 2e 31 39 37 34 37 39 2c 2d 33 33 2e 38 32 35 35 30 2c 31 38 2c 31 32 30 2c 39 30 2c 38 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 32 38 38 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 39 31 33 2c 32 31 35 2c 30 30 2c 66 63 2c 36 38 2c 30 30 2c 36 31 2c 32 34 62 63 2c 31 63 2c 35 35 2c 30 30 30 30 2c 36 30 2c 30 30 31 64 2c 30 30 30 30 2c 30 30 2c 36 34 2c 33 37 63 32 2c 34 35 2c 35 33 2c 30 30 30 30 2c 30 30 30 30 2c 30 30 2c 30 30 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 39 31 35 2c 31 35 31 2e 31 39 37 36 33 31 2c 2d 33 33 2e 38 32 35 34 32 2c 32 37 2c 36 38 2c 39 31 2c 38 2c 31 38 30 2c 31 2c 30 2c 31 34 2e 33 30 34 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 39 31 35 2c 32 33 33 2c 30 2c 30 2c 31 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 39 31 35 2c 31 35 31 2e 31 39 37 36 33 31 2c 2d 33 33 2e 38 32 35 34 32 2c 32 37 2c 36 38 2c 39 31 2c 38 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 33 30 34 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 39 31 35 2c 32 33 33 2c 30 30 2c 66 63 2c 36 38 2c 30 30 2c 36 31 2c 31 66 62 34 2c 32 31 2c 35 35 2c 30 30 30 30 2c 33 30 2c 30 30 32 32 2c 30 30 30 30 2c 30 30 2c 36 34 2c 33 37 63 32 2c 34 35 2c 33 61 2c 30 30 30 30 2c 30 30 30 30 2c 30 30 2c 30 30 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 39 32 31 2c 31 35 31 2e 31 39 38 31 30 34 2c 2d 33 33 2e 38 32 35 30 37 2c 32 36 2c 34 33 2c 38 34 2c 38 2c 31 39 39 2c 31 2c 30 2c 31 34 2e 33 30 34 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 39 32 32 2c 32 39 35 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 39 32 32 2c 31 35 31 2e 31 39 38 31 33 35 2c 2d 33 33 2e 38 32 35 30 35 2c 31 36 2c 34 32 2c 38 31 2c 38 2c 32 30 30 2c 31 2c 30 2c 31 34 2e 33 30 34 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 39 32 32 2c 32 39 35 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 39 32 32 2c 31 35 31 2e 31 39 38 31 33 35 2c 2d 33 33 2e 38 32 35 30 35 2c 31 36 2c 34 32 2c 38 31 2c 38 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 33 30 34 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 39 32 32 2c 32 39 35 2c 30 30 2c 36 63 2c 36 62 2c 30 30 2c 32 39 2c 30 61 65 30 2c 30 32 2c 35 35 2c 30 30 30 30 2c 32 30 2c 30 30 32 62 2c 30 30 30 30 2c 30 30 2c 36 34 2c 33 37 63 32 2c 34 35 2c 32 35 2c 30 30 30 30 2c 30 30 30 30 2c 30 30 2c 30 30 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 34 39 32 34 2c 31 35 31 2e 31 39 38 31 39 36 2c 2d 33 33 2e 38 32 34 39 39 2c 37 2c 34 33 2c 38 35 2c 38 2c 31 36 33 2c 31 2c 30 2c 31 34 2e 32 37 32 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 34 39 32 34 2c 33 30 35 2c 31 33 30 2c 32 37 2c 32 37 0d 0a 33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 37 35 37 32 38 2c 31 35 31 2e 31 37 38 39 32 34 2c 2d 33 33 2e 38 30 38 31 36 2c 38 2c 32 33 36 2c 31 30 35 2c 30 2c 31 31 2c 30 2c 30 2c 31 33 2e 31 36 38 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 37 35 37 32 32 2c 33 34 37 37 0d 0a';
 
         // 300 message imei = 355233051406492        
         //str="33 35 35 32 33 33 30 35 31 34 30 36 34 39 32 2c 32 30 31 33 30 35 30 34 30 36 32 35 32 35 2c 31 35 31 2e 31 39 35 37 32 34 2c 2d 33 33 2e 38 32 33 31 39 2c 30 2c 31 35 34 2c 39 32 2c 37 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 32 32 34 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 30 34 30 36 32 35 32 35 2c 33 30 37 31 2c 30 30 2c 36 31 2c 38 37 2c 30 30 2c 32 36 2c 30 61 31 63 2c 30 30 2c 35 38 2c 30 30 30 30 2c 31 65 2c 30 31 63 65 2c 30 30 30 30 2c 30 30 2c 36 34 2c 33 37 35 63 2c 34 35 2c 32 35 2c 30 30 30 30 2c 30 30 30 33 2c 30 30 2c 30 30 0d 0a";
         //str = "33 35 31 38 30 32 30 35 34 32 38 37 37 38 38 2c 32 30 31 33 30 35 31 34 30 31 30 33 30 37 2c 31 35 31 2e 30 37 34 34 33 32 2c 2d 33 34 2e 30 30 30 39 38 2c 30 2c 33 32 39 2c 36 35 2c 30 2c 33 30 30 2c 31 2c 30 2c 31 34 2e 31 32 38 2c 34 2e 30 39 36 2c 32 30 31 33 30 35 31 34 30 31 30 33 30 35 2c 30 2c 30 30 2c 36 33 2c 30 30 2c 30 30 2c 30 30 2c 30 66 34 63 2c 30 32 2c 30 30 2c 30 34 37 65 2c 32 39 2c 30 30 30 30 2c 30 30 30 30 2c 30 30 2c 30 30 2c 30 30 30 30 2c 30 30 2c 30 30 2c 30 30 32 61 2c 30 30 31 65 2c 30 30 2c 30 30 0d 0a";
         //buf = generate_buf(str);

         console.log("-----------------------------------------------START--------------------------------------------------------------------------------------------");
         remote_ip = req.remoteAddress;
         console.log('Remote IP - '+remote_ip);
         // Sending commands
         
         var buf_arr = (str_replace(buf.toString('utf8', start=0, end=buf.length),'\r\n','')).split(',');
         var imei = parse_imei(buf_arr);
         console.log("IMEI = "+imei);
           if (imei){
              // Set current ip to current imei
              var query = "UPDATE `commands` SET `ip`='"+remote_ip+"' WHERE `imei`='"+imei+"'";
              console.log("Query = "+query);
              connection.query(query, function(err,data) {
                                  if (err) {throw err;
                                   }
                                   });
              
              query = "UPDATE `devices` SET `last_ip`='"+remote_ip+"' WHERE `imei`='"+imei+"'";
              console.log("Query = "+query);
              connection.query(query, function(err,data) {
                                  if (err) {throw err;
                                   }
                                   });

              query = "SELECT * FROM `commands` WHERE `imei`='"+imei+"' AND `status`='0' ORDER BY `id` ASC";
              console.log("Query = "+query);
              connection.query(query, function(err,data) {
                                  if (err) {throw err;
                                   }
                                  if (data){
                                    if (data.length>0)
                                    {
                                        var send_command = generate_buf(data[0].command);
                                        var command_id = data[0].id;
                                        console.log("Send command to device - "+send_command.toString('utf8', start=0, end=send_command.length));
                                        req.write(send_command);
                                          // Set command status to sent
                                        query = "UPDATE `commands` SET `status`='1' WHERE `imei`='"+imei+"' AND `id`='"+command_id+"'";
                                        console.log("Query = "+query);
                                            connection.query(query, function(err,data) {
                                                            if (err) {throw err;
                                                             }
                                                             });
                                    }
                    

                                  } 
                                   });
                    }
           
  
         // End sending commands
         

         console.log("Main string in HEX:");
         var str = "";
         for (var i=0;i<=buf.length;i++) {
          str=str + ' ' +simple_base_convert(buf[i],10,16);
         }
         console.log(str);
         
        // Send reponse
        temp_arr = generate_string_from_buf(buf).split(' ');

        if ((temp_arr[0]=='fa') && (temp_arr[1]=='f8'))
        {
          req.write(buf);
          console.log("Send Heartbeat message:"+buf);
        }
        else
        {
            
           var main_res_arr = buf_filter(buf);
           
              for (var key in main_res_arr)
              {
                 
                 buf = main_res_arr[key];
                 buf = implode(' ',buf);
                 buf = generate_buf(buf);
          
                 // Parse buffer
                 var main_str_arr = (str_replace(buf.toString('utf8', start=0, end=buf.length),'\r\n','')).split(',');
    
                 var gps_arr = new Array;
                 var odb_arr = new Array;
                 for(var i=0;i<main_str_arr.length;i++)
                 {
                  if (i<15)
                  {       
                     gps_arr.push(main_str_arr[i]);
                  }
                  else
                  {
                     odb_arr.push(main_str_arr[i]);
                  }   
                 }
                     if (verify_odb_array_on_digits(odb_arr))
                     {
                        odb_arr = make_correct_odb(odb_arr);
                     }
        
                     
                     
                  if (verify_odb_array_on_integrity(odb_arr) && (odb_arr.length>=19))
                  {
                     
                     GPSdata_arr = parse_GPSdata(gps_arr);
                     ODBdata_arr = parse_ODBdata(odb_arr);
                     console.log("Main string in ASCII:");
                     console.log(implode(' ',main_str_arr));
                     //console.log("GPS string in ASCII:");
                     //console.log(implode(' ',gps_arr));
                     //console.log("ODB data string:");
                     //console.log(implode(' ',odb_arr));
                     console.log("GPS array:");
                     console.log(GPSdata_arr);
                     console.log("ODB array:");
                     console.log(ODBdata_arr);
                     
                
                     var data2 = ODBdata_arr['Total fuel used']*1000;               
                     redis.set(GPSdata_arr['Modem_ID']+'.'+'fuel_used',data2);

                      
                      var time = GPSdata_arr['GPS_DataTime'];

                       query = "SELECT `odometer` FROM `devices` WHERE `imei`='"+GPSdata_arr['Modem_ID']+"'";
                                           connection.query(query, function(err,data1) {
                                                            if (err) {throw err;
                                                             }
                                                              var odometer = data1[0].odometer;

                                                                query = "SELECT * FROM `schedules` WHERE `device_imei`='"+GPSdata_arr['Modem_ID']+"' AND `is_alert`=0";
                                                                        connection.query(query, function(err,data) {
                                                                                        if (err) {throw err;}
                                                                                        if (data.length>0){
                                                                                              for (var i=0;i<data.length;i++)
                                                                                              {
                                                                                                 if (data[i].date_due){
                                                                                                      var date1 = gen_array_for_mktime(time);
                                                                                                      var date2 = gen_array_for_mktime(dateFormat(data[i].date_due,"yyyy-mm-dd h:MM:ss"));
                                                                                                      var time_duration = mktime(date2[0],date2[1],date2[2],date2[3],date2[4],date2[5]) - mktime(date1[0],date1[1],date1[2],date1[3],date1[4],date1[5]); 
                                                                                                      if (time_duration<=604800)
                                                                                                      {
                                                                                                         var schedule_data = '?';
                                                                                                             schedule_data+='imei='+GPSdata_arr['Modem_ID'];
                                                                                                             schedule_data+='&time_duration='+time_duration;
                                                                                                             schedule_data+='&name='+data[i].name;
                                                                                                             schedule_data+='&type=schedule';
                                                                                                     
                                                                                                         send_data_to_kohana(schedule_data);

                                                                                                         query="UPDATE `schedules` SET `is_alert`=1 WHERE `device_imei`='"+GPSdata_arr['Modem_ID']+"'";
                                                                                                         connection.query(query, function(err,data) {
                                                                                                                             if (err) {throw err;}
                                                                                                                           });
                                                                                                      }
                                                                                                    }

                                                                                                 
                                                                                                 if (data[i].mil_due){
                                                                                                      var mileage_duration = data[i].mil_due - odometer;
                                                                                                      if (mileage_duration<=500)
                                                                                                      {
                                                                                                        var schedule_data = '?';
                                                                                                             schedule_data+='imei='+GPSdata_arr['Modem_ID'];
                                                                                                             schedule_data+='&mileage_duration='+mileage_duration;
                                                                                                             schedule_data+='&name='+data[i].name;
                                                                                                             schedule_data+='&type=schedule';
                                                                                                     
                                                                                                         send_data_to_kohana(schedule_data);

                                                                                                        query="UPDATE `schedules` SET `is_alert`=1 WHERE `device_imei`='"+GPSdata_arr['Modem_ID']+"'";
                                                                                                         connection.query(query, function(err,data) {
                                                                                                                             if (err) {throw err;}
                                                                                                                           });
                                                                                                      }
                                                                                                 }      
                                                                                              }
                                                                                            }
                                                                                            
                                                                                         });
                                                             });          
                                   
                        // Validate for engine faults
                        if (ODBdata_arr['(MIL)status']==='ON'){
                          redis.get(GPSdata_arr['Modem_ID']+'.'+'trip_start',function(err,trip_start){
                          redis.get(GPSdata_arr['Modem_ID']+'.'+'istrip',function(err,ontrip){
                               if (ontrip !== trip_start) {
                              redis.INCR(GPSdata_arr['Modem_ID']+'.'+'engine_faults');
                              // Send AT$OBDGDTC? command to get engine error type
                               var command_id = generate_buf("41 54 24 4F 42 44 47 44 54 43 3F");
                               console.log("Send command to device with IMEI - "+GPSdata_arr['Modem_ID']+" command - "+command_id.toString('utf8', start=0, end=command_id.length));
                               req.write(command_id);

                               query = "INSERT IGNORE INTO `errCommands`(`imei`,`ip`,`command`) VALUES ('"+GPSdata_arr['Modem_ID']+"','"+remote_ip+"','41 54 24 4F 42 44 47 44 54 43 3F')";
                                            console.log("Query = "+query);
                                                connection.query(query, function(err,data) {
                                                                if (err) {throw err;
                                                                 }
                                                                 });

                                     redis.set(GPSdata_arr['Modem_ID']+'.'+'istrip',trip_start);
                               }
                          });
                          });
                        }

                        sql = "INSERT IGNORE into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`,`MIL_status`,`number_of_DTC_codes`,`Engine_load`,`Engine_coolant`,`Fuel_pressure`,`Intake_manifold_pressure`,`Engine_RPM`,`ODB_Speed`,`Intake_temp`,`MAF_air_flow_rate`,`Throttle_position`,`Run_time_since_engine_start`,`Distance_travelled_with_error`,`Fuel_level`,`Barometric_pressure`,`Control_module_voltage`,`Air_temp`,`Accel_pedal_pos`,`Total_fuel_used`,`OBD_Odometer`) VALUES ('"+GPSdata_arr['Modem_ID']+"','"+GPSdata_arr['GPS_DataTime']+"','"+GPSdata_arr['Longitude']+"','"+GPSdata_arr['Latitude']+"','"+GPSdata_arr['Speed']+"','"+GPSdata_arr['Direction']+"','"+GPSdata_arr['Altitude']+"','"+GPSdata_arr['Satellites']+"','"+GPSdata_arr['Message_ID']+"','"+GPSdata_arr['Input_Status']+"','"+GPSdata_arr['Output_Status']+"','"+GPSdata_arr['Analog_Input1']+"','"+GPSdata_arr['Analog_Input2']+"','"+GPSdata_arr['RTC_DataTime']+"','"+GPSdata_arr['Mileage']+"','"+ODBdata_arr['(MIL)status']+"','"+ODBdata_arr['number_of_DTC_codes']+"','"+ODBdata_arr['Engine load']+"','"+ODBdata_arr['Engine coolant']+"','"+ODBdata_arr['Fuel pressure']+"','"+ODBdata_arr['Intake manifold pressure']+"','"+ODBdata_arr['Engine RPM']+"','"+ODBdata_arr['ODB Speed']+"','"+ODBdata_arr['Intake temp']+"','"+ODBdata_arr['MAF air flow rate']+"','"+ODBdata_arr['Throttle position']+"','"+ODBdata_arr['Run time since engine start']+"','"+ODBdata_arr['Distance travelled with error']+"','"+ODBdata_arr['Fuel level']+"','"+ODBdata_arr['Barometric pressure']+"','"+ODBdata_arr['Control module voltage']+"','"+ODBdata_arr['Air temp']+"','"+ODBdata_arr['Accel pedal pos']+"','"+ODBdata_arr['Total fuel used']+"','"+ODBdata_arr['OBD Odometer']+"')";
                        //console.log(sql);
                        //return 0;

                        connection.query(sql, function(err) {
                        if (err) {throw err;
                        console.log(err);
                         }
                         });
                        
                  }
                  else 
                        if (main_str_arr.length==15)
                        { 
                         //console.log("main_str - "+main_str_arr);
                         var Message_ID = getMessageID(main_str_arr);
                             console.log("Message_ID - "+Message_ID);
                             
                          // Calculate scores
                              if (Message_ID==11)
                              {
                                var data = new Array;
                                data = parse_11_data(main_str_arr);
                                console.log(Message_ID+" string in Data:");
                                console.log(data);
                                if (data['Input_Status']==1)
                                {

                                     redis.set(data['Modem_ID']+'.'+'score',100);
                                     redis.set(data['Modem_ID']+'.'+'alerts',0);
                                     redis.set(data['Modem_ID']+'.'+'fuel_used',0);
                                     redis.set(data['Modem_ID']+'.'+'engine_faults',0);
                                     console.log('Set score to 100 and alerts to 0');
                                     redis.set(data['Modem_ID']+'.'+'trip_start',data['GPS_DataTime']);
                                     redis.set(data['Modem_ID']+'.'+'start_latitude',data['Latitude']);
                                     redis.set(data['Modem_ID']+'.'+'start_longitude',data['Longitude']);

                                     redis.set(data['Modem_ID']+'.'+'istrip',0);
                                }
                                else 
                                {
                                  redis.get(data['Modem_ID']+'.'+'score',function(err,data1){
                                       redis.get(data['Modem_ID']+'.'+'trip_start',function(err,data2){
                                           redis.get(data['Modem_ID']+'.'+'alerts',function(err,data3){
                                              redis.get(data['Modem_ID']+'.'+'fuel_used',function(err,data4){
                                                 redis.get(data['Modem_ID']+'.'+'engine_faults',function(err,data5){
                                                    redis.get(data['Modem_ID']+'.'+'start_latitude',function(err,data6){
                                                      redis.get(data['Modem_ID']+'.'+'start_longitude',function(err,data7){     
                                                         console.log('DATA1 - '+data1);
                                                         console.log('DATA2 - '+data2);
                                                         console.log('DATA3 - '+data3);
                                                         console.log('DATA4 - '+data4);
                                                         console.log('DATA5 - '+data5);
                                                         console.log('DATA6 - '+data6);
                                                         console.log('DATA7 - '+data7);


                                                         var date_start_arr = gen_array_for_mktime(data2);
                                                         var date_end_arr = gen_array_for_mktime(data['GPS_DataTime']);
                                                         var fuel_used = data4;
                                   
                                                         var time_duration = mktime(date_end_arr[0],date_end_arr[1],date_end_arr[2],date_end_arr[3],date_end_arr[4],date_end_arr[5]) - mktime(date_start_arr[0],date_start_arr[1],date_start_arr[2],date_start_arr[3],date_start_arr[4],date_start_arr[5]); 
                                                         if ((data['Mileage']<=1500) || (time_duration<=60) || (data3<4)) data1 = 'N/A';
                                                         if (data1<0) data1 = 0;
                                                         data4 = data4/1000;
                                                         
                                                         var trip_data = '?';
                                                             trip_data+='imei='+data['Modem_ID'];
                                                             trip_data+='&duration='+time_duration;
                                                             trip_data+='&score='+data1;
                                                             trip_data+='&alerts='+data3;
                                                             trip_data+='&trip_start='+data2;
                                                             trip_data+='&trip_end='+data['GPS_DataTime'];
                                                             trip_data+='&mileage='+data['Mileage'];
                                                             trip_data+='&fuel_used='+data4;
                                                             trip_data+='&engine_faults='+data5;
                                                             trip_data+='&type=trip_data';

                                                         send_data_to_kohana(trip_data);
                                                        
                                                        // Save Odometer Value
                                                        query = "SELECT `odometer` FROM `devices` WHERE `imei`='"+data['Modem_ID']+"'";
                                                        connection.query(query, function(err,data8) {
                                                            if (err) {throw err;
                                                             }
                                                             var odometer = parseFloat((parseInt(data['Mileage'])/1000).toFixed(2)) + parseFloat(data8[0].odometer);
                                                                 odometer = odometer.toFixed(2);
                                                

                                                        sql = "INSERT IGNORE into `scores` (`imei`,`score`,`alerts`,`trip_start`,`trip_end`,`mileage`,`fuel_used`,`engine_faults`,`odometer`,`start_latitude`,`start_longitude`,`end_latitude`,`end_longitude`) VALUES ('"+data['Modem_ID']+"','"+data1+"','"+data3+"','"+data2+"','"+data['GPS_DataTime']+"','"+data['Mileage']+"','"+data4+"','"+data5+"','"+odometer+"','"+data6+"','"+data7+"','"+data['Latitude']+"','"+data['Longitude']+"')";
                                                        console.log('Save score to MySQL');
                                                        console.log(sql);
                                                        
                                                        connection.query(sql, function(err) {if (err) {throw err;}});

                                                        sql = "UPDATE `devices` SET `odometer`='"+odometer+"' WHERE `imei`='"+data['Modem_ID']+"'";
                                                        connection.query(sql, function(err) {if (err) {throw err;}})

                                                        });
                                             });
                                            });
                                           }); 
                                          });
                                         }); 
                                        });
                                      }); 
                                }
                  
                              }                              
                              else if ((Message_ID==206) || (Message_ID==208) || (Message_ID==162) || (Message_ID==199))
                              {
                               
                                console.log('HELLO i am in 162 or 199 or 206 or 208');
                                var data = new Array;
                                data = parse_11_data(main_str_arr);
                                redis.DECRBY(data['Modem_ID']+'.'+'score',5);
                                redis.INCR(data['Modem_ID']+'.'+'alerts');
                                console.log("Message_ID "+Message_ID+" DECRBY and INCR");

                                if (Message_ID==162)
                                {
                                  var alert_data = "?imei="+data['Modem_ID']+"&alert=OVER SPEED ALERT&type=alerts";     
                                    send_data_to_kohana(alert_data);
                                }
                              }


                         GPSdata_arr = parse_GPSdata(gps_arr);
                         console.log("Main string in ASCII:");
                         console.log(implode(' ',main_str_arr));
                         console.log("GPS string in ASCII:");
                         console.log(implode(' ',gps_arr));
                         console.log("GPS array:");
                         console.log(GPSdata_arr);
                
                        sql = "INSERT IGNORE into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`) VALUES ('"+GPSdata_arr['Modem_ID']+"','"+GPSdata_arr['GPS_DataTime']+"','"+GPSdata_arr['Longitude']+"','"+GPSdata_arr['Latitude']+"','"+GPSdata_arr['Speed']+"','"+GPSdata_arr['Direction']+"','"+GPSdata_arr['Altitude']+"','"+GPSdata_arr['Satellites']+"','"+GPSdata_arr['Message_ID']+"','"+GPSdata_arr['Input_Status']+"','"+GPSdata_arr['Output_Status']+"','"+GPSdata_arr['Analog_Input1']+"','"+GPSdata_arr['Analog_Input2']+"','"+GPSdata_arr['RTC_DataTime']+"','"+GPSdata_arr['Mileage']+"')";
                        //console.log(sql);
                        //return 0;
                        }
                        else
                           {

                              // Get message Type_ID
                              var Message_ID = getMessageID(main_str_arr);
                                console.log("Message_ID - "+Message_ID);
                                console.log("String in ASCII - "+main_str_arr); 
     
                             // MSG TYPE: 59 FTP Download OK
                              if ((Message_ID==59) || (Message_ID==60) || (Message_ID==61) || (Message_ID==62))
                              {
                                var data = new Array;
                                data = parse_59_data(main_str_arr);
                                console.log(Message_ID+" string in Data:");
                                console.log(data);
                                sql = "INSERT IGNORE into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`,`Text`) VALUES ('"+data['Modem_ID']+"','"+data['GPS_DataTime']+"','"+data['Longitude']+"','"+data['Latitude']+"','"+data['Speed']+"','"+data['Direction']+"','"+data['Altitude']+"','"+data['Satellites']+"','"+data['Message_ID']+"','"+data['Input_Status']+"','"+data['Output_Status']+"','"+data['Analog_Input1']+"','"+data['Analog_Input2']+"','"+data['RTC_DataTime']+"','"+data['Mileage']+"','"+data['Text']+"')";   
                              }
                              // MSG TYPE: 321 OVER RPM ALERT 
                              else if (Message_ID==321)
                              {
                                var data = new Array;
                                data = parse_321_data(main_str_arr);
                                console.log("321 string in Data:");
                                console.log(data);
                                redis.DECRBY(data['Modem_ID']+'.'+'score',5);
                                redis.INCR(data['Modem_ID']+'.'+'alerts');
                                var alert_data = "?imei="+data['Modem_ID']+"&alert='Over RPM Alert'&data=Over_RPM - "+data['Over_RPM']+"&type=alerts";     
                                    send_data_to_kohana(alert_data);
                                console.log("Message_ID "+Message_ID+" DECRBY and INCR");
                                sql = "INSERT IGNORE into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`,`Over_RPM`) VALUES ('"+data['Modem_ID']+"','"+data['GPS_DataTime']+"','"+data['Longitude']+"','"+data['Latitude']+"','"+data['Speed']+"','"+data['Direction']+"','"+data['Altitude']+"','"+data['Satellites']+"','"+data['Message_ID']+"','"+data['Input_Status']+"','"+data['Output_Status']+"','"+data['Analog_Input1']+"','"+data['Analog_Input2']+"','"+data['RTC_DataTime']+"','"+data['Mileage']+"','"+data['Over_RPM']+"')";   
                              }
                              // SG TYPE: 322 ENGINE OVER HEATED ALERT 
                              else if (Message_ID==322)
                              {
                                var data = new Array;
                                data = parse_322_data(main_str_arr);
                                console.log("322 string in Data:");
                                console.log(data);
                                redis.INCR(data['Modem_ID']+'.'+'alerts');
                                var alert_data = "?imei="+data['Modem_ID']+"&alert=ENGINE OVER HEATED ALERT&data=Over_TEMP - "+data['Over_TEMP']+"&type=alerts";     
                                    send_data_to_kohana(alert_data);
                                sql = "INSERT IGNORE into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`,`Over_TEMP`) VALUES ('"+data['Modem_ID']+"','"+data['GPS_DataTime']+"','"+data['Longitude']+"','"+data['Latitude']+"','"+data['Speed']+"','"+data['Direction']+"','"+data['Altitude']+"','"+data['Satellites']+"','"+data['Message_ID']+"','"+data['Input_Status']+"','"+data['Output_Status']+"','"+data['Analog_Input1']+"','"+data['Analog_Input2']+"','"+data['RTC_DataTime']+"','"+data['Mileage']+"','"+data['Over_TEMP']+"')";   
                              }  
                              // MSG TYPE: 323 Accelerator over stepping Alert 
                              else if (Message_ID==323)
                              {
                                var data = new Array;
                                data = parse_323_data(main_str_arr);
                                console.log("323 string in Data:");
                                console.log(data);
                                redis.DECRBY(data['Modem_ID']+'.'+'score',5);
                                redis.INCR(data['Modem_ID']+'.'+'alerts');
                                var alert_data = "?imei="+data['Modem_ID']+"&alert=Accelerator over stepping Alert&data=Over_STEP - "+data['Over_STEP']+"&type=alerts";     
                                    send_data_to_kohana(alert_data);
                                console.log("Message_ID "+Message_ID+" DECRBY and INCR");
                                sql = "INSERT IGNORE into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`,`Over_STEP`) VALUES ('"+data['Modem_ID']+"','"+data['GPS_DataTime']+"','"+data['Longitude']+"','"+data['Latitude']+"','"+data['Speed']+"','"+data['Direction']+"','"+data['Altitude']+"','"+data['Satellites']+"','"+data['Message_ID']+"','"+data['Input_Status']+"','"+data['Output_Status']+"','"+data['Analog_Input1']+"','"+data['Analog_Input2']+"','"+data['RTC_DataTime']+"','"+data['Mileage']+"','"+data['Over_STEP']+"')";   
                              }    
                              // MSG TYPE: 180 IMPACT DETECT Alert (Possible Car accident)
                              else if (Message_ID==180)
                              {
                                var data = new Array;
                                data = parse_180_data(main_str_arr);
                                console.log("180 string in Data:");
                                console.log(data);
                                redis.INCR(data['Modem_ID']+'.'+'alerts');
                                var alert_data = "?imei="+data['Modem_ID']+"&alert=IMPACT DETECT Alert&data=X_Axis : "+data['X_Axis']+" Y_Axis : "+data['Y_Axis']+" Z_Axis : "+data['Z_Axis']+"&type=alerts";     
                                    send_data_to_kohana(alert_data);

                                sql = "INSERT IGNORE into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`,`X_Axis`,`Y_Axis`,`Z_Axis`) VALUES ('"+data['Modem_ID']+"','"+data['GPS_DataTime']+"','"+data['Longitude']+"','"+data['Latitude']+"','"+data['Speed']+"','"+data['Direction']+"','"+data['Altitude']+"','"+data['Satellites']+"','"+data['Message_ID']+"','"+data['Input_Status']+"','"+data['Output_Status']+"','"+data['Analog_Input1']+"','"+data['Analog_Input2']+"','"+data['RTC_DataTime']+"','"+data['Mileage']+"','"+data['X_Axis']+"','"+data['Y_Axis']+"','"+data['Z_Axis']+"')";   
                              }  
                              // MSG TYPE: 160  Device Power up Alert (First plugged into Car)
                              else if (Message_ID==160)
                              {
                                var data = new Array;
                                data = parse_160_data(main_str_arr);
                                console.log("160 string in Data:");
                                console.log(data);
                                redis.INCR(data['Modem_ID']+'.'+'alerts');
                                
                                var alert_data = "?imei="+data['Modem_ID']+"&alert=Device Power up Alert&type=alerts";     
                                    send_data_to_kohana(alert_data);

                                sql = "INSERT IGNORE into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`,`Main_Battery`,`Back_Up_Battery`) VALUES ('"+data['Modem_ID']+"','"+data['GPS_DataTime']+"','"+data['Longitude']+"','"+data['Latitude']+"','"+data['Speed']+"','"+data['Direction']+"','"+data['Altitude']+"','"+data['Satellites']+"','"+data['Message_ID']+"','"+data['Input_Status']+"','"+data['Output_Status']+"','"+data['Analog_Input1']+"','"+data['Analog_Input2']+"','"+data['RTC_DataTime']+"','"+data['Mileage']+"','"+data['Main_Battery']+"','"+data['Back_Up_Battery']+"')";   
                              }
                              // MSG TYPE: 163 High Speed Alert 
                              else if (Message_ID==163)
                              {
                                var data = new Array;
                                data = parse_163_data(main_str_arr);
                                console.log("163 string in Data:");
                                console.log(data);
                                redis.INCR(data['Modem_ID']+'.'+'alerts');
                                 var alert_data = "?imei="+data['Modem_ID']+"&alert=High Speed Alert&data=MAX_Speed : "+data['MAX_Speed']+" MAX_Speed_Duration : "+data['MAX_Speed_Duration']+" AVG_Speed : "+data['AVG_Speed']+"&type=alerts";     
                                    send_data_to_kohana(alert_data);

                                sql = "INSERT IGNORE into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`,`MAX_Speed`,`AVG_Speed`,`MAX_Speed_Duration`) VALUES ('"+data['Modem_ID']+"','"+data['GPS_DataTime']+"','"+data['Longitude']+"','"+data['Latitude']+"','"+data['Speed']+"','"+data['Direction']+"','"+data['Altitude']+"','"+data['Satellites']+"','"+data['Message_ID']+"','"+data['Input_Status']+"','"+data['Output_Status']+"','"+data['Analog_Input1']+"','"+data['Analog_Input2']+"','"+data['RTC_DataTime']+"','"+data['Mileage']+"','"+data['MAX_Speed']+"','"+data['AVG_Speed']+"','"+data['MAX_Speed_Duration']+"')";   
                              }
                              // MSG TYPE: 170 Device Removal Alert (Device Unplugged from Car)
                              else if ((Message_ID==170) || (Message_ID==175))
                              {
                                var data = new Array;
                                data = parse_160_data(main_str_arr);
                                console.log("170 or 175 string in Data:");
                                console.log(data);
                                redis.INCR(data['Modem_ID']+'.'+'alerts');
                                var alert_data = "?imei="+data['Modem_ID']+"&alert=Device Removal Alert&type=alerts";     
                                    send_data_to_kohana(alert_data);
                                sql = "INSERT IGNORE into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`,`Main_Battery`,`Back_Up_Battery`) VALUES ('"+data['Modem_ID']+"','"+data['GPS_DataTime']+"','"+data['Longitude']+"','"+data['Latitude']+"','"+data['Speed']+"','"+data['Direction']+"','"+data['Altitude']+"','"+data['Satellites']+"','"+data['Message_ID']+"','"+data['Input_Status']+"','"+data['Output_Status']+"','"+data['Analog_Input1']+"','"+data['Analog_Input2']+"','"+data['RTC_DataTime']+"','"+data['Mileage']+"','"+data['Main_Battery']+"','"+data['Back_Up_Battery']+"')";   
                                
                              }

                              else if ((Message_ID==161) || (Message_ID==191) || (Message_ID==210))
                              {
                                var data = new Array;
                                data = parse_161_data(main_str_arr);
                                console.log("161 or 191 or 210 string in Data:");
                                console.log(data);
                                redis.INCR(data['Modem_ID']+'.'+'alerts');
                                sql = "INSERT IGNORE into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`,`geo_fence_index`) VALUES ('"+data['Modem_ID']+"','"+data['GPS_DataTime']+"','"+data['Longitude']+"','"+data['Latitude']+"','"+data['Speed']+"','"+data['Direction']+"','"+data['Altitude']+"','"+data['Satellites']+"','"+data['Message_ID']+"','"+data['Input_Status']+"','"+data['Output_Status']+"','"+data['Analog_Input1']+"','"+data['Analog_Input2']+"','"+data['RTC_DataTime']+"','"+data['Mileage']+"','"+data['geo_fence_index']+"')";   
                              
                              }

                              else
                              {
                                if (typeof(Message_ID)==='undefined')
                                { 
                                   response+=' '+main_str_arr;
                                }
                              }
                           }
                   
                           if (sql!='')
                           {
                           connection.query(sql, function(err) {
                                  if (err) {throw err;
                                   }
                                   });
                           }
                           
               }
                //console.log("Response = "+response);

               // Save response command
              if (response!='')
              { 
                   var myRegExp = /OBDGDTC/;
                   console.log("Verify response on engine_faults. Response - "+response);
                   if (myRegExp.test(response)){
                      
                       console.log("Response was verified - "+response);
                       query = "UPDATE `errCommands` SET `response`='"+response+"' WHERE `ip`='"+remote_ip+"'";
                          console.log("Save engine_faults response");
			                    console.log("Query = "+query);
			                  connection.query(query, function(err,data) {
			                                   if (err) {throw err;}
			                                  });
                        query = "SELECT * FROM `errCommands` WHERE `ip`='"+remote_ip+"'";
                                console.log("Query = "+query);
                                connection.query(query, function(err,data) {
                                       var err_data = '?';
                                           err_data+='imei='+data[0].imei;
                                           err_data+='&type=engineError';
                                   
                                       send_data_to_kohana(err_data);
                                  });
                   } else {
                            console.log("Device response - "+response);  
                            query = "UPDATE `commands` SET `response`='"+response+"', `status`='2' WHERE `ip`='"+remote_ip+"'";
                            console.log("Query = "+query);
                                connection.query(query, function(err,data) {
                                                 if (err) {throw err;}
                                                });
                              // Save to history
                             query = "SELECT * FROM `commands` WHERE `ip`='"+remote_ip+"'";
                             console.log("Query = "+query);
                                connection.query(query, function(err,data) {
                                                 if (err) {throw err;}
                                                 if (data)
                                                 {
                                                  if (data.length>0)
                                                  {
                                                      query = "INSERT IGNORE INTO `historycommands`(`imei`,`ip`,`command`,`response`) VALUES ('"+data[0].imei+"','"+remote_ip+"','"+data[0].command+"','"+data[0].response+"')";
                                                      console.log("Query = "+query);
                                                          connection.query(query, function(err,data) {
                                                                          if (err) {throw err;
                                                                           }
                                                                           });
                                                  }
                                                 }
                                                });

                   }

                     response='';

              }

              console.log("--------------------------------------------END------------------------------------------------------------------------------------");        
      }
           
   });
});

server.listen(port, function() { //'listening' listener
  console.log('Server waiting for connections on port '+port+' ...');
});





