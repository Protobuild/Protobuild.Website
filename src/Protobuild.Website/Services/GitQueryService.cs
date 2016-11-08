using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices;
using System.Threading.Tasks;
using Protobuild.Website.Models;

namespace Protobuild.Website.Services
{
    public class GitQueryService : IGitQueryService
    {
        public async Task<List<BranchModel>> GetBranches(PackageModel package)
        {
            var startInfo = new ProcessStartInfo();

            startInfo.UseShellExecute = false;
            startInfo.Arguments = "ls-remote --heads " + package.GitUrl;

            if (RuntimeInformation.IsOSPlatform(OSPlatform.Windows))
            {
                startInfo.FileName = "C:\\Program Files\\Git\\bin\\git.exe";
            }
            else
            {
                startInfo.FileName = "/usr/bin/git";
            }

            startInfo.CreateNoWindow = true;
            startInfo.RedirectStandardOutput = true;
            startInfo.RedirectStandardInput = true;

            var process = Process.Start(startInfo);

            process.StandardInput.Dispose();

            string[] lines;
            using (var reader = process.StandardOutput)
            {
                lines = (await reader.ReadToEndAsync()).Split(new string[] { "\r\n", "\n", "\r"}, StringSplitOptions.RemoveEmptyEntries);
            }

            process.WaitForExit();

            var results = new List<BranchModel>();
            foreach (var line in lines)
            {
                var components = line.Split('\t');
                if (components.Length >= 2)
                {
                    var branchName = components[1].Trim();

                    if (branchName.StartsWith("refs/heads/"))
                    {
                        branchName = branchName.Substring("refs/heads/".Length);
                    }
                    else
                    {
                        continue;
                    }

                    results.Add(new BranchModel
                    {
                        BranchName = branchName,
                        IsAutoBranch = true,
                        VersionName = components[0].Trim(),
                        PackageName = package.Name
                    });
                }
            }


            return results;
        }
    }
}
