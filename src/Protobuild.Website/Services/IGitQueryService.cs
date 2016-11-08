using System.Collections.Generic;
using System.Threading.Tasks;
using Protobuild.Website.Models;

namespace Protobuild.Website.Services
{
    public interface IGitQueryService
    {
        Task<List<BranchModel>> GetBranches(PackageModel package);
    }
}
