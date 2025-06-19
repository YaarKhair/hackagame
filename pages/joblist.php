 <?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
		// Random string
			// Function
				function rand_string( $length ) {
					$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";	
					$size = strlen( $chars );
					$str = "";
						for( $i = 0; $i < $length; $i++ ) {
							$str .= $chars[ rand( 0, $size - 1 ) ];
						}
					return $str;
				}
		$random_number = rand(1,20); // Used to generate random number of forms
				
        // see if we may do a job or if we are too soon
       if ($now <= $hackerdata['nextjob_date'])
			return "Your system is not yet ready for another job.";

         // fetch joblist , which depends on the rank and the EP
         $query = "SELECT * FROM jobs ORDER BY difficulty ASC";
         $result = mysqli_query($link, $query);
         if (mysqli_num_rows($result) == 0)
                 return "There are currently no jobs available.";

         $_SESSION['dojob'] = 1;

         echo '
                         <h1>Hack jobs for pocket change</h1>
                         <div class="row th light-bg">
                            <div class="col w80">Job</div>
                            <div class="col w20">Success rate</div>
                         </div>
						 <div class="dark-bg">';

				// HIDE THE FAKE SHIT
				echo '<div style="display:none">';
				
				// Random Forms at the beginning
			    for ($i = 0;$i < $random_number;$i++)
    				echo '<form><input type="radio" name="'.rand_string(4).'"></form>', chr(10);
						 
				// First Honeypot
					echo '<form name="hf_form2" method="POST" action="index.php">
						  <input type="hidden" name="h" value="dojob">';
						
						for ($i = 0;$i <= 10; $i++) 
							echo '<input type="radio" name="job_id" ID="'.$i.'" value="'.$i.'">', chr(10);

					echo '<input type="submit" value="Try this job">
						  </form>';
				 
				// Second Honeypot
					echo '<form name="SMA11HACKF0RM" method="POST" action="index.php">
						  <input type="hidden" name="h" value="dojob">';
						
						for ($i = 0;$i <= 10; $i++) 
							echo '<input type="radio" name="job_id" ID="'.$i.'" value="'.$i.'">', chr(10);

					echo '<input type="submit" value="Try this job">
						  </form>';
						  
				// Third Honeypot
					echo '<form name="hf_form" method="POST" action="index.php">
						  <input type="hidden" name="h" value="dojob">';
						
						for ($i = 0;$i <= 10; $i++) 
							echo '<input type="radio" name="job_id" ID="'.$i.'" value="'.$i.'">', chr(10);
					
						echo '<input type="submit" value="Try this job">
						  </form>';
				// STOP HIDING	
				echo '</div>';
				
				// Displaying the ACTUAL FORM
				echo '<FORM ACTION="index.php" METHOD="POST" class="alt-design">
					  <input type="hidden" name="h" value="dojob">';

         while ($row = mysqli_fetch_assoc($result)) {
                 // success?
                 $success_rate = intval(100 - ($row['difficulty'] * 10) + ((EP2Level(GetHackerEP($hackerdata['id'])) / 10) + 1 * 1.5));
                 if ($success_rate > 99) { $success_rate = 99; }

                 // auto select your previous job.
                 $checked = '';
                 if (isset($_SESSION['job_id'])) if ($row['id'] == $_SESSION['job_id']) $checked = ' checked'; // auto select previous chosen job
                 if ($row['id'] == mysqli_num_rows($result) && !isset($_SESSION['job_id'])) $checked = ' checked'; // if the session is not set, select the last job in the list
                         echo '<div class="row hr-light light-bg">
                                                 <div class="col w80">
                                                         <input type="radio" name="jobid" ID="'.'job_'.$row['id'].'" value="'.$row['id'].'"'.$checked.'><label for="'.'job_'.$row['id'].'">'.$row['description'].'</label>
                                                 </div>
                                                 <div class="col w20">'.$success_rate.'</div>
                                         </div>';
        }
        
        AddFormHash ("job");
                
                echo '                  <div class="row">
                                                 <div class="col w100">
                                                        <input type="submit" value="Try this job" class="light-bg">
                                                 </div>
                                         </div>
                                 </FORM></div>';
								 
				// random forms at the end
				echo '<div style="display:none">';
				for ($i = 0;$i < $random_number;$i++)
					echo '<form><input type="radio" name="'.rand_string(4).'"></form>', chr(10);
				echo '</div>';

 ?>
