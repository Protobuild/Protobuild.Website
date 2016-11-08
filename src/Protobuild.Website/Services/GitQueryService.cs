using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices;
using System.Threading.Tasks;
using Protobuild.Website.Models;
using Microsoft.Extensions.Caching.Distributed;
using Newtonsoft.Json;

namespace Protobuild.Website.Services
{
    public class GitQueryService : IGitQueryService
    {
        private readonly IDistributedCache _distributedCache;

        private DistributedCacheEntryOptions _distributedCacheOptions =
            new DistributedCacheEntryOptions()
                .SetAbsoluteExpiration(TimeSpan.FromSeconds(30));

        public GitQueryService(IDistributedCache distributedCache)
        {
            _distributedCache = distributedCache;
        }

        public async Task<List<BranchModel>> GetBranches(PackageModel package)
        {
            var cachedValue = await _distributedCache.GetStringAsync("gitBranches:" + package.GitUrl);
            if (cachedValue != null)
            {
                return JsonConvert.DeserializeObject<string[]>(cachedValue).Select(x => BranchModel.FromJsonCache(x)).ToList();
            }

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

            await _distributedCache.SetStringAsync(
                "gitBranches:" + package.GitUrl, 
                JsonConvert.SerializeObject(results.Select(x => x.ToJsonCache()).ToArray()),
                _distributedCacheOptions);

            return results;
        }
    }
}
