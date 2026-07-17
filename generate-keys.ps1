$rsa = [System.Security.Cryptography.Ed25519]::Create()
$privateKey = $rsa.ExportPkcs8PrivateKey()
$publicKey = $rsa.ExportSubjectPublicKeyInfo()

$privateKeyPem = '-----BEGIN PRIVATE KEY-----' + [Environment]::NewLine + 
    ([System.Convert]::ToBase64String($privateKey) -replace '(.{64})', '$1' + [Environment]::NewLine) + 
    '-----END PRIVATE KEY-----'

$publicKeyPem = 'ssh-ed25519 ' + [Convert]::ToBase64String($publicKey) + ' github-actions-nyalife-hms'

[System.IO.File]::WriteAllText('github-actions-nyalifehms', $privateKeyPem)
[System.IO.File]::WriteAllText('github-actions-nyalifehms.pub', $publicKeyPem)

Write-Host 'Keys generated successfully'