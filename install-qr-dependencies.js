// Install QR code dependencies for Node.js
const fs = require('fs');
const { exec } = require('child_process');

console.log('Installing QR code dependencies...');

// Check if package.json exists
if (!fs.existsSync('package.json')) {
    // Create package.json
    const packageJson = {
        name: 'halovpbank2025-qr',
        version: '1.0.0',
        description: 'QR Code generation for VPBank Solution Day Game',
        main: 'index.js',
        dependencies: {
            qrcode: '^1.5.3',
        },
        scripts: {
            test: 'echo "Error: no test specified" && exit 1',
        },
    };

    fs.writeFileSync('package.json', JSON.stringify(packageJson, null, 2));
    console.log('✅ Created package.json');
}

// Install qrcode package
exec('npm install qrcode', (error, stdout, stderr) => {
    if (error) {
        console.error('❌ Error installing qrcode:', error);
        return;
    }

    console.log('✅ qrcode package installed successfully');
    console.log(stdout);

    if (stderr) {
        console.log('Warnings:', stderr);
    }

    // Test the installation
    const QRCode = require('qrcode');

    QRCode.toDataURL('test', (err, url) => {
        if (err) {
            console.error('❌ QR code test failed:', err);
        } else {
            console.log('✅ QR code generation test successful');
            console.log('📦 Installation complete!');
        }
    });
});
