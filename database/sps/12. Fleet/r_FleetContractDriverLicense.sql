CREATE or alter PROC [dbo].[r_FleetContractDriverLicense]

AS
BEGIN
    SET NOCOUNT ON;
	   CREATE TABLE #FleetContraactDriverLicense          
    (                  
        Fullname  VARCHAR(200),            
        LicenseNo   VARCHAR(200),
		Category VARCHAR(200),
		Issued DATE,                              
        Expiry   DATE,    
		Notes VARCHAR(200)
 
    );  

	INSERT INTO #FleetContraactDriverLicense
	 (                  
        Fullname,          
        LicenseNo,
		Category,
		Issued ,                              
        Expiry,    
		Notes 
	 )
	 SELECT 
	 a.Fullname,
	 d.LicenseNumber,
	 d.LicenseCategory,
	 d.IssueDate ,
	 d.Expirydate,
	 d.Notes
	 
	 FROM t_ContractedDriverLicenses AS d 
	 join t_ContractedDrivers AS a on d.ContractedDriverID = a.Id

	   SELECT * FROM #FleetContraactDriverLicense
	DROP TABLE #FleetContraactDriverLicense

	END




	
	--EXEC [r_FleetContractDriverLicense]