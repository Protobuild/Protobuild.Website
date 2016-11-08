using Microsoft.AspNetCore.Mvc;
using Protobuild.Website.Authorization;
using Protobuild.Website.Models;
using Protobuild.Website.Services;
using Protobuild.Website.ViewModels;
using System.Collections.Generic;
using System.Threading.Tasks;
using Protobuild.Website.ApiMiddleware;

namespace Protobuild.Website.Controllers
{
    public class AccountController : Controller
    {
        private readonly IRepository _repository;

        public AccountController(IRepository repository)
        {
            _repository = repository;
        }

        [Route("/{user}")]
        public async Task<IActionResult> Index(string user)
        {
            var userModel = await _repository.LoadUserByName(user);

            // TODO: Model with packages
            return View(new UserViewModel
            {
                User = userModel,
                Packages = new List<PackageModel>(),
                Owners = new List<UserModel>()
            });
        }

        [Api]
        [Route("/{user}/api")]
        public async Task<IActionResult> IndexApi(string user)
        {
            var userModel = await _repository.LoadUserByName(user);

            return Json(new
            {
                user = userModel.ToJsonObject()
            });
        }

        [ProtobuildAuthorized]
        [Route("/{user}/rename")]
        public IActionResult Rename(string user)
        {
            return View();
        }

        [ProtobuildAuthorized]
        [Route("/{user}/owner/add")]
        public IActionResult OwnershipAdd(string user)
        {
            return View();
        }

        [ProtobuildAuthorized]
        [Route("/{user}/owner/remove")]
        public IActionResult OwnershipRemove(string user, string owner)
        {
            return View();
        }
    }
}
