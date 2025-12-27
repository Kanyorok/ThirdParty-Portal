$baseUrl = "http://localhost:8000/api/v1/portal/auth"

# Step 1: Login
Write-Host "=== Step 1: Login ===" -ForegroundColor Cyan
$loginBody = @{ email = "kitongameshack9@gmail.com"; password = "Dahir@12*" } | ConvertTo-Json
$loginResponse = Invoke-RestMethod -Method Post -Uri "$baseUrl/login" -ContentType "application/json" -Body $loginBody
$token = $loginResponse.token
Write-Host "Token Acquired." -ForegroundColor Green

# Step 2: Complete Profile
Write-Host "`n=== Step 2: Complete Profile ===" -ForegroundColor Cyan
$completeBody = @{
    ThirdPartyName     = "ShakTech Ltd"
    TradingName        = "ShakTech Ltd"
    BusinessType       = 45
    RegistrationNumber = "PVT-KEN-123456"
    TaxPIN             = "P012345678X"
    CountryId          = 1
    LocationId         = 1
    PhysicalAddress    = "Nairobi, Kenya"
    accountType        = "supplier"
    supplierCategories = @(1)
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Method Post -Uri "$baseUrl/complete-profile" `
        -ContentType "application/json" `
        -Headers @{ Authorization = "Bearer $token"; Accept = "application/json" } `
        -Body $completeBody
    $response | ConvertTo-Json -Depth 5
}
catch {
    Write-Host "`n=== Backend Error Detected (500) ===" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        # Modern PowerShell 7+ way to read the error body
        $task = $_.Exception.Response.Content.ReadAsStringAsync()
        $task.Wait()
        $content = $task.Result
        try {
            $content | ConvertFrom-Json | ConvertTo-Json -Depth 5
        }
        catch {
            Write-Host $content -ForegroundColor Yellow
        }
    }
    else {
        Write-Host $_.Exception.Message -ForegroundColor Yellow
    }
}