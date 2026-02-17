CREATE OR ALTER PROC [dbo].[r_FleetDriverLicenseTracking]

AS
BEGIN
    SET NOCOUNT ON;
	   CREATE TABLE #FleetDriverLicenseTracking                 
    (                  
                     
        LicenseNo   VARCHAR(200),
		Category VARCHAR(200),
		Issued DATE,                              
        Expiry   DATE,    
		Notes VARCHAR(200)
 
    );  

	INSERT INTO #FleetDriverLicenseTracking
	 (                  
                      
        LicenseNo,
		Category,
		Issued ,                              
        Expiry,    
		Notes 
	 )
	 SELECT 
	 d.LicenseNumber,
	 d.LicenseCategory,
	 d.IssueDate ,
	 d.Expirydate,
	 d.Notes
	 
	 FROM t_FleetDriverLicenseTracking AS d 

	   SELECT * FROM #FleetDriverLicenseTracking
	DROP TABLE #FleetDriverLicenseTracking

	END



