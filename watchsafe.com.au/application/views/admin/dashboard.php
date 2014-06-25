<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
               		<script type="text/javascript">
               		$(document).ready(function() {
                           getAction();						   
                    });
                    function getAction()
                           {						  
						    var dashboard = $("#dashboard");
							dashboard.load('/ajax/getDashboardAdmin');
							setTimeout('getAction()','20000');
						   }
					</script>
					
                    <div id='dashboard'></div>   
