-- Make data_source larger to accept pasted JSON lists for matrix inputs
ALTER TABLE `form_inputs` MODIFY `data_source` TEXT DEFAULT NULL;