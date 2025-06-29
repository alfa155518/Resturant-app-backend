
<!DOCTYPE html>
<html>
<head>
    <title>New Contact Message</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .details { margin: 20px 0; }
        .details p { margin: 10px 0; }
        .message { background-color: #f9f9f9; padding: 15px; border-left: 4px solid #3498db; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>New Contact Form Submission</h2>
    </div>
    

    <div class="details">
        <p><strong>Name:</strong> {{ $contactData['name'] ?? 'Not provided' }}</p>
        <p><strong>Email:</strong> <a href="mailto:{{ $contactData['email'] }}">{{ $contactData['email'] ?? 'Not provided' }}</a></p>
        <p><strong>Phone Number:</strong> {{ $contactData['phone'] ?? 'Not provided' }}</p>
        <p><strong>Subject:</strong> {{ $contactData['subject'] ?? 'No subject' }}</p>
    </div>
    
    <div class="message">
        <h3>Message:</h3>
        <p>{{ $contactData['message'] ?? 'No message content.' }}</p>
    </div>
</body>
</html>
