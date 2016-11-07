using Protobuild.Website.Models;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Protobuild.Website.Services
{
    public interface IRepository
    {
        Task<UserModel> LoadUserByName(string name);

        Task<IEnumerable<PackageModel>> LoadAllPackagesForUser(UserModel user);
    }
}
