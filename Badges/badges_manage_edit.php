<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//Module includes
include './modules/Badges/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Badges/badges_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
            ->add(__('Manage Badges'),'badges_manage.php')
            ->add(__('Edit Badges'));    

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $badgesBadgeID = $_GET['badgesBadgeID'];
    if ($badgesBadgeID == '') { echo "<div class='error'>";
        echo 'You have not specified a policy.';
        echo '</div>';
    } else {
        try {
            $data = array('badgesBadgeID' => $badgesBadgeID);
            $sql = 'SELECT * FROM badgesBadge WHERE badgesBadgeID=:badgesBadgeID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo 'The selected policy does not exist.';
            echo '</div>';
        } else {
            //Let's go!
            $row = $result->fetch();

            if ($_GET['search'] != '' || $_GET['category'] != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Badges/badges_manage.php&search='.$_GET['search'].'&category='.$_GET['category']."'>Back to Search Results</a>";
                echo '</div>';
            }
            ?>
			<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL']."/modules/Badges/badges_manage_editProcess.php?badgesBadgeID=$badgesBadgeID&search=".$_GET['search']."&category=".$_GET['category'] ?>" enctype="multipart/form-data">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">
					<tr>
						<td>
							<b>Name *</b><br/>
						</td>
						<td class="right">
							<input name="name" id="name" maxlength=50 value="<?php echo htmlPrep($row['name']) ?>" type="text" style="width: 300px">
							<script type="text/javascript">
								var name=new LiveValidation('name');
								name.add(Validate.Presence);
							</script>
						</td>
					</tr>
					<tr>
						<td>
							<b>Active *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<select name="active" id="active" style="width: 302px">
								<option <?php if ($row['active'] == 'Y') { echo 'selected'; } ?> value="Y">Y</option>
								<option <?php if ($row['active'] == 'N') { echo 'selected'; } ?> value="N">N</option>
							</select>
						</td>
					</tr>
					<?php
                    $categories = getSettingByScope($connection2, 'Badges', 'badgeCategories');
					$categories = explode(',', $categories);
					?>
					<tr>
						<td>
							<b><?php echo __('Category') ?> *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<select name="category" id="category" style="width: 302px">
								<option value="Please select..."><?php echo __('Please select...') ?></option>
								<?php
                                for ($i = 0; $i < count($categories); ++$i) {
                                    $selected = '';
                                    if ($row['category'] == $categories[$i]) {
                                        $selected = 'selected';
                                    }
                                    ?>
									<option <?php echo $selected ?> value="<?php echo trim($categories[$i]) ?>"><?php echo trim($categories[$i]) ?></option>
								<?php

                                }
            					?>
							</select>
							<script type="text/javascript">
								var category=new LiveValidation('category');
								category.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "<?php echo __('Select something!') ?>"});
							</script>
						</td>
					</tr>
					<tr>
						<td>
							<b>Description</b><br/>
						</td>
						<td class="right">
							<textarea name='description' id='description' rows=5 style='width: 300px'><?php echo htmlPrep($row['description']) ?></textarea>
						</td>
					</tr>
					<tr>
						<td>
							<b>Logo</b><br/>
							<span style="font-size: 90%"><i><?php echo __('240px x 240px').'<br/>' ?>
							<?php if ($row['logo'] != '') { echo __('Will overwrite existing attachment.'); } ?>
							</i></span>
						</td>
						<td class="right">
							<?php
                            if ($row['logo'] != '') {
                                echo __('Current attachment:')." <a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/'.$row['logo']."'>".$row['logo'].'</a><br/><br/>'; } ?>
							<input type="file" name="file" id="file">
							<script type="text/javascript">
								var file=new LiveValidation('file');
								file.add( Validate.Inclusion, { within: ['gif','jpg','jpeg','png'], failureMessage: "Illegal file type!", partialMatch: true, caseSensitive: false } );
							</script>
						</td>
					</tr>
					<tr>
						<td>
							<b>Logo License/Credits</b><br/>
						</td>
						<td class="right">
							<textarea name='logoLicense' id='logoLicense' rows=5 style='width: 300px'><?php echo htmlPrep($row['logoLicense']) ?></textarea>
						</td>
					</tr>

					<tr>
						<td>
							<span style="font-size: 90%"><i>* denotes a required field</i></span>
						</td>
						<td class="right">
							<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
							<input type="submit" value="Submit">
						</td>
					</tr>
				</table>
			</form>
			<?php

        }
    }
}
?>
