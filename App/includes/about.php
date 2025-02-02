                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <?php include_once(__DIR__ . "/../../App/Modules/Names/names.php"); if (!isset($personnelNames) || !is_array($personnelNames)) { echo "Error: personnelNames array is not initialized."; } ?>
<div class="container mt-2 d-flex justify-content-center">
    <div class="card" style="width: 70%;">
        <div class="card-header text-center" style="background-color: #20263e; color: #ffffff; font-size: 25px; font-weight: bolder;">About Us</div>
        <div class="card-body" style="color: #20263e;">
            <h5 class="text-end fw-bolder" style="font-size: 25px; font-weight: bolder;">Development</h5>

            <div class="mb-3 text-end">
                <p class="text-end first-line-indent">This system is under the patent of DepEd - Schools Division of Bayawan City under the supervision of <span style="font-weight: bolder;"><?php echo $personnelNames[0]; ?></span>. Using this system without the written authorized consent of the said owner shall be punishable by law.</p>
                
                <p class="text-end first-line-indent">This system is intended for report submission of School Heads of all schools in Bayawan City (including Basay). It was developed by <span style="font-weight: bolder;"><?php echo $personnelNames[1]; ?></span>, an ICT OJT Student, with the assistance of <span style="font-weight: bolder;"><?php echo $personnelNames[2]; ?></span>, the IT Officer I of SDO Bayawan.</p>
                
                <p class="text-end mb-0 first-line-indent">This system shall be officially used by the Records Office for document management and reporting purposes.</p>
            </div>
        </div>
    </div>
</div>
