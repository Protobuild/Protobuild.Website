using System;

namespace Protobuild.Website
{
    public static class ProtobuildEnv
    {
        public static string GetDomain()
        {
            return Environment.GetEnvironmentVariable("DOMAIN")
                   ?? "http://localhost:57827";
        }

        public static string GetServiceAccountPath()
        {
            return Environment.GetEnvironmentVariable("GOOGLE_SERVICE_ACCOUNT_JSON_PATH")
                ?? "Credentials/ServiceAccount.json";
        }

        public static string GetOAuthClientPath()
        {
            return Environment.GetEnvironmentVariable("GOOGLE_OAUTH_CLIENT_JSON_PATH")
                ?? "Credentials/OAuthClient.json";
        }

        public static string GetProjectId()
        {
            return Environment.GetEnvironmentVariable("GOOGLE_PROJECT_ID")
                ?? "protobuild-index";
        }

        public static string MakeApiUrl(string url)
        {
            return url + "/api";
        }
    }
}
