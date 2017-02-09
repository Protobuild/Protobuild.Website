using Microsoft.AspNetCore.Mvc;
using Protobuild.Website.Authorization;
using Protobuild.Website.Services;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Protobuild.Website.ApiMiddleware;
using Protobuild.Website.Models;
using Protobuild.Website.ViewModels;

namespace Protobuild.Website.Controllers
{
    public class PackageController : Controller
    {
        private readonly IRepository _repository;
        private readonly IGitQueryService _gitQueryService;

        public PackageController(IRepository repository, IGitQueryService gitQueryService)
        {
            _repository = repository;
            _gitQueryService = gitQueryService;
        }

        [Route("/{user}/{package}")]
        public async Task<IActionResult> Index(string user, string package, bool branch = false, bool uploaded = false)
        {
            var result = await _repository.LoadUserAndPackageByNames(user, package);

            List<BranchModel> branches = null;
            List<VersionModel> versions = null;

            // TODO: This is really inefficient and causes lots of Datastore reads.  We should change our API to fix this.
            await Task.WhenAll(new[] {
                Task.Run(async () => { branches = await GetBranchesForPackage(result.User, result.Package); }),
                Task.Run(async () => { versions = await _repository.LoadAllVersionsForPackage(result.User, result.Package); })
            });

            var viewModel = new PackageViewModel
            {
                User = result.User,
                Package = result.Package,
                Branches = branches,
                Versions = versions,
                JustUploaded = uploaded,
                JustCreatedBranch = branch
            };

            return View(viewModel);
        }

        [Api]
        [Route("/{user}/{package}/api")]
        public async Task<IActionResult> IndexApi(string user, string package)
        {
            var result = await _repository.LoadUserAndPackageByNames(user, package);

            List<BranchModel> branches = null;
            List<VersionModel> versions = null;

            // TODO: This is really inefficient and causes lots of Datastore reads.  We should change our API to fix this.
            await Task.WhenAll(new[] {
                Task.Run(async () => { branches = await GetBranchesForPackage(result.User, result.Package); }),
                Task.Run(async () => { versions = await _repository.LoadAllVersionsForPackage(result.User, result.Package); })
            });

            return Json(new
            {
                user = result.User.ToJsonObject(),
                package = result.Package.ToJsonObject(result.User),
                branches = branches.Select(x => x.ToJsonObject()).ToArray(),
                versions = versions.Select(x => x.ToJsonObject()).ToArray(),
            });
        }

        private async Task<List<BranchModel>> GetBranchesForPackage(UserModel user, PackageModel package)
        {
            if (string.IsNullOrWhiteSpace(package.GitUrl))
            {
                return await _repository.LoadAllBranchesForPackage(user, package);
            }

            return await _gitQueryService.GetBranches(package);
        }

        [ProtobuildAuthorized]
        [Route("/{user}/{package}/edit")]
        public IActionResult Edit(string user, string package)
        {
            return View();
        }

        [ProtobuildAuthorized]
        [Route("/{user}/{package}/delete")]
        public IActionResult Delete(string user, string package)
        {
            return View();
        }

        [ProtobuildAuthorized]
        [Route("/{user}/{package}/version/new")]
        public IActionResult VersionNew(string user, string package)
        {
            return View();
        }

        [ProtobuildAuthorized]
        [Route("/{user}/{package}/version/upload/{id}")]
        public IActionResult VersionUpload(string user, string package, string id)
        {
            return View();
        }

        [ProtobuildAuthorized]
        [Route("/{user}/{package}/version/delete/{id}")]
        public IActionResult VersionDelete(string user, string package, string id)
        {
            return View();
        }

        [ProtobuildAuthorized]
        [Route("/{user}/{package}/branch/new")]
        public IActionResult BranchNew(string user, string package, string id)
        {
            return View();
        }

        [ProtobuildAuthorized]
        [Route("/{user}/{package}/branch/edit/{name}")]
        public IActionResult BranchEdit(string user, string package, string name)
        {
            return View();
        }

        [ProtobuildAuthorized]
        [Route("/{user}/{package}/branch/delete/{name}")]
        public IActionResult BranchDelete(string user, string package, string name)
        {
            return View();
        }
    }
}
