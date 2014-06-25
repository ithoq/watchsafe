var net = require('net');
var port = 1338;
var mysql      = require('mysql');
var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'alexey72408512QWaszx',
  database : 'mattgps',
});

 connection.connect();
//-----------------------------------------------------Functions------------------------------------------------------------------------------------------------------///

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

function implode( glue, pieces ) { 
  return ( ( pieces instanceof Array ) ? pieces.join ( glue ) : pieces );
}

function generate_string_from_buf(buf)
{
  str='';
  for (i=0;i<buf.length;i++)
  {
       str+=base_convert(buf[i],10,16)+' ';
  }
  return str;
}

function str_replace(haystack, needle, replacement) { 
  var temp = haystack.split(needle); 
  return temp.join(replacement); 
} 

function var_dump(obj) {
    var out = "";
    if(obj && typeof(obj) == "object"){
        for (var i in obj) {
            out += i + ": " + obj[i] + "\n";
        }
    } else {
        out = obj;
    }
    return out;
}

function simple_base_convert(number, frombase, tobase){
  res = parseInt(number, frombase).toString(tobase);
  return res;
 }

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


 function parse_GPSdata(temp_arr)
 {
         var data_arr = new Array;
         data_arr['Modem_ID'] = temp_arr[0];
         data_arr['GPS_DataTime'] = temp_arr[1];
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
         data_arr['RTC_DataTime'] = temp_arr[13];
         data_arr['Mileage'] = temp_arr[14];
         return data_arr;
 }

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

//------------------------------------------------------End Functions-------------------------------------------------------------------------------------------------///
//var counter = 0;
var server = net.createServer(function(c) { //'connection' listener
  console.log('Client connected...');
  c.on('data', function(buf){
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
         //buf = generate_buf(str);
         
         console.log("-----------------------------------------------START--------------------------------------------------------------------------------------------");
         console.log("Main string in HEX:");
         console.log(buf);
        // Send reponse
        temp_arr = generate_string_from_buf(buf).split(' ');

        if ((temp_arr[0]=='fa') && (temp_arr[1]=='f8'))
        {
          c.write(buf);
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
                     console.log("GPS string in ASCII:");
                     console.log(implode(' ',gps_arr));
                     console.log("ODB data string:");
                     console.log(implode(' ',odb_arr));
                     console.log("GPS array:");
                     console.log(GPSdata_arr);
                     console.log("ODB array:");
                     console.log(ODBdata_arr);
                
                     
                        
                        sql = "INSERT into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`,`MIL_status`,`number_of_DTC_codes`,`Engine_load`,`Engine_coolant`,`Fuel_pressure`,`Intake_manifold_pressure`,`Engine_RPM`,`ODB_Speed`,`Intake_temp`,`MAF_air_flow_rate`,`Throttle_position`,`Run_time_since_engine_start`,`Distance_travelled_with_error`,`Fuel_level`,`Barometric_pressure`,`Control_module_voltage`,`Air_temp`,`Accel_pedal_pos`,`Total_fuel_used`,`OBD_Odometer`) VALUES ('"+GPSdata_arr['Modem_ID']+"','"+GPSdata_arr['GPS_DataTime']+"','"+GPSdata_arr['Longitude']+"','"+GPSdata_arr['Latitude']+"','"+GPSdata_arr['Speed']+"','"+GPSdata_arr['Direction']+"','"+GPSdata_arr['Altitude']+"','"+GPSdata_arr['Satellites']+"','"+GPSdata_arr['Message_ID']+"','"+GPSdata_arr['Input_Status']+"','"+GPSdata_arr['Output_Status']+"','"+GPSdata_arr['Analog_Input1']+"','"+GPSdata_arr['Analog_Input2']+"','"+GPSdata_arr['RTC_DataTime']+"','"+GPSdata_arr['Mileage']+"','"+ODBdata_arr['(MIL)status']+"','"+ODBdata_arr['number_of_DTC_codes']+"','"+ODBdata_arr['Engine load']+"','"+ODBdata_arr['Engine coolant']+"','"+ODBdata_arr['Fuel pressure']+"','"+ODBdata_arr['Intake manifold pressure']+"','"+ODBdata_arr['Engine RPM']+"','"+ODBdata_arr['ODB Speed']+"','"+ODBdata_arr['Intake temp']+"','"+ODBdata_arr['MAF air flow rate']+"','"+ODBdata_arr['Throttle position']+"','"+ODBdata_arr['Run time since engine start']+"','"+ODBdata_arr['Distance travelled with error']+"','"+ODBdata_arr['Fuel level']+"','"+ODBdata_arr['Barometric pressure']+"','"+ODBdata_arr['Control module voltage']+"','"+ODBdata_arr['Air temp']+"','"+ODBdata_arr['Accel pedal pos']+"','"+ODBdata_arr['Total fuel used']+"','"+ODBdata_arr['OBD Odometer']+"')";
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

                         GPSdata_arr = parse_GPSdata(gps_arr);
                         console.log("Main string in ASCII:");
                         console.log(implode(' ',main_str_arr));
                         console.log("GPS string in ASCII:");
                         console.log(implode(' ',gps_arr));
                         console.log("GPS array:");
                         console.log(GPSdata_arr);
                
                        sql = "INSERT into `2locations` (`Modem_ID`,`GPS_DataTime`,`Longitude`,`Latitude`,`Speed`,`Direction`,`Altitude`,`Satellites`,`Message_ID`,`Input_Status`,`Output_Status`,`Analog_Input1`,`Analog_Input2`,`RTC_DataTime`,`Mileage`) VALUES ('"+GPSdata_arr['Modem_ID']+"','"+GPSdata_arr['GPS_DataTime']+"','"+GPSdata_arr['Longitude']+"','"+GPSdata_arr['Latitude']+"','"+GPSdata_arr['Speed']+"','"+GPSdata_arr['Direction']+"','"+GPSdata_arr['Altitude']+"','"+GPSdata_arr['Satellites']+"','"+GPSdata_arr['Message_ID']+"','"+GPSdata_arr['Input_Status']+"','"+GPSdata_arr['Output_Status']+"','"+GPSdata_arr['Analog_Input1']+"','"+GPSdata_arr['Analog_Input2']+"','"+GPSdata_arr['RTC_DataTime']+"','"+GPSdata_arr['Mileage']+"')";
                        //console.log(sql);
                        //return 0;

                        connection.query(sql, function(err) {
                        if (err) {throw err;
                        console.log(err);
                         }
                         });                      
                        }
                        else
                           {
                             console.log("Broken data...");
                           }
                     console.log("--------------------------------------------END------------------------------------------------------------------------------------");
               }
              
      }
           
   });
});

server.listen(port, function() { //'listening' listener
  console.log('Server waiting for connections on port '+port+' ...');
});





