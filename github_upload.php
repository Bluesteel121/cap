<?php
/**
 * GitHub API Integration for Image Uploads
 * 
 * This function uploads an image file to a GitHub repository.
 * It should be called after successfully saving the image locally.
 * 
 * @param string $local_file_path Path to the local file
 * @param string $github_file_path Path where file should be saved in GitHub
 * @return bool|string Returns true on success or error message on failure
 */
function uploadToGitHub($local_file_path, $github_file_path) {
    // GitHub API Configuration
    $github_username = 'Bluesteel121';
    $github_repo = 'cap';
    $github_branch = 'main'; // or your default branch name
    $github_token = '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'; // Store this securely, not in code
    
    // Read the file content
    $file_content = file_get_contents($local_file_path);
    if ($file_content === false) {
        return "Failed to read local file";
    }
    
    // Base64 encode the content for GitHub
    $content_encoded = base64_encode($file_content);
    
    // Prepare the API request data
    $api_url = "https://api.github.com/repos/{$github_username}/{$github_repo}/contents/{$github_file_path}";
    $commit_message = "Upload profile picture via web app";
    
    $post_data = json_encode([
        'message' => $commit_message,
        'content' => $content_encoded,
        'branch' => $github_branch
    ]);
    
    // Initialize cURL session
    $ch = curl_init($api_url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: PHP Script',
        'Content-Type: application/json',
        'Authorization: token ' . $github_token,
        'Accept: application/vnd.github.v3+json'
    ]);
    
    // Execute cURL session
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Close cURL session
    curl_close($ch);
    
    // Check for errors
    if ($http_code >= 200 && $http_code < 300) {
        return true;
    } else {
        $response_data = json_decode($response, true);
        return "GitHub API Error: " . ($response_data['message'] ?? 'Unknown error');
    }
}