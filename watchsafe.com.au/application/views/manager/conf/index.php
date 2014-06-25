     <div class="hero-unit" style="height: 400px;">
        <h3>User configurations:</h3>
           <table>
             <tr>
                <td>
	            <form class="navbar-form pull-left" method="post" accept-charset="utf-8" action="/manager/conf">
	              <button type="submit" name="change_password_show" class="btn btn-info">Change Password</button>
	            </form>
	            </td>
	            <td> 
	            <form class="navbar-form pull-left" method="post" accept-charset="utf-8" action="/manager/vehicle">
	              <button type="submit" class="btn btn-info">Setup Vehicles</button>
	            </form>
	            </td>
	            <td>
	            <form class="navbar-form pull-left" method="post" accept-charset="utf-8" action="/manager/fences">
	              <button type="submit" class="btn btn-info">Setup Watch Zones</button>
	            </form>
	            </td>
	            <td>
	            <form class="navbar-form pull-left" method="get" accept-charset="utf-8" action="/manager/users">
	              <button type="submit" class="btn btn-info">Members</button>
	            </form>
	            </td>
	         </tr>
            </table>    
            
      </div>