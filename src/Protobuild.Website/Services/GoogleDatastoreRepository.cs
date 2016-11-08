﻿using Google.Datastore.V1;
using System;
using Protobuild.Website.Models;
using System.Collections.Generic;
using System.Threading.Tasks;
using System.Linq;
using Protobuild.Website.Exceptions;

namespace Protobuild.Website.Services
{
    public class GoogleDatastoreRepository : IRepository
    {
        private DatastoreDb _db;

        private KeyFactory _userKeyFactory;

        private KeyFactory _packageKeyFactory;

        public GoogleDatastoreRepository()
        {
            Environment.SetEnvironmentVariable(
                "GOOGLE_APPLICATION_CREDENTIALS",
                ProtobuildEnv.GetServiceAccountPath());
            Environment.SetEnvironmentVariable(
                "GOOGLE_PROJECT_ID",
                ProtobuildEnv.GetProjectId());

            _db = DatastoreDb.Create(ProtobuildEnv.GetProjectId());
            _userKeyFactory = _db.CreateKeyFactory(UserModel.Kind);
            _packageKeyFactory = _db.CreateKeyFactory(PackageModel.Kind);
        }

        public async Task<IEnumerable<PackageModel>> LoadAllPackagesForUser(UserModel user)
        {
            throw new NotImplementedException();
        }

        public async Task<UserModel> LoadUserByName(string name)
        {
            if (name == null)
            {
                throw new NullReferenceException(nameof(name));
            }

            var query = new Query(UserModel.Kind)
            {
                Filter = Filter.Equal("canonicalName", name),
                Limit = 1
            };

            var result = _db.RunQueryLazilyAsync(query, ReadOptions.Types.ReadConsistency.Eventual);
            using (var enumerator = result.GetEnumerator())
            {
                while (await enumerator.MoveNext())
                {
                    var entity = enumerator.Current;

                    return MapUser(entity);
                }
            }

            throw new Protobuild404Exception(CommonErrors.USER_NOT_FOUND);
        }

        public async Task<UserAndPackageResult> LoadUserAndPackageByNames(string user, string package)
        {
            if (user == null)
            {
                throw new NullReferenceException(nameof(user));
            }

            if (package == null)
            {
                throw new NullReferenceException(nameof(package));
            }

            var userModel = await LoadUserByName(user);
            
            var query = new Query(PackageModel.Kind)
            {
                Filter = Filter.And(Filter.Equal("googleID", userModel.GoogleId), Filter.Equal("name", package)),
                Limit = 1
            };

            PackageModel packageModel = null;

            var result = _db.RunQueryLazilyAsync(query, ReadOptions.Types.ReadConsistency.Eventual);
            using (var enumerator = result.GetEnumerator())
            {
                while (packageModel == null && await enumerator.MoveNext())
                {
                    var entity = enumerator.Current;

                    packageModel = MapPackage(entity);
                }
            }

            if (packageModel == null)
            {
                throw new Protobuild404Exception(CommonErrors.PACKAGE_NOT_FOUND);
            }

            return new UserAndPackageResult
            {
                User = userModel,
                Package = packageModel
            };
        }

        public async Task<List<BranchModel>> LoadAllBranchesForPackage(UserModel user, PackageModel package, int? limit = null)
        {
            if (user == null)
            {
                throw new NullReferenceException(nameof(user));
            }

            if (package == null)
            {
                throw new NullReferenceException(nameof(package));
            }

            var results = new List<BranchModel>();

            var query = new Query(BranchModel.Kind)
            {
                Filter = Filter.And(Filter.Equal("googleID", user.GoogleId), Filter.Equal("packageName", package.Name)),
                Limit = limit
            };

            var result = _db.RunQueryLazilyAsync(query, ReadOptions.Types.ReadConsistency.Eventual);
            using (var enumerator = result.GetEnumerator())
            {
                while (await enumerator.MoveNext())
                {
                    var entity = enumerator.Current;

                    results.Add(MapBranch(entity));
                }
            }

            return results;
        }

        public async Task<List<VersionModel>> LoadAllVersionsForPackage(UserModel user, PackageModel package, int? limit = null)
        {
            if (user == null)
            {
                throw new NullReferenceException(nameof(user));
            }

            if (package == null)
            {
                throw new NullReferenceException(nameof(package));
            }

            var results = new List<VersionModel>();

            var query = new Query(VersionModel.Kind)
            {
                Filter = Filter.And(Filter.Equal("googleID", user.GoogleId), Filter.Equal("packageName", package.Name)),
                Limit = limit
            };

            var result = _db.RunQueryLazilyAsync(query, ReadOptions.Types.ReadConsistency.Eventual);
            using (var enumerator = result.GetEnumerator())
            {
                while (await enumerator.MoveNext())
                {
                    var entity = enumerator.Current;

                    results.Add(MapVersion(entity));
                }
            }

            return results;
        }

        private UserModel MapUser(Entity entity)
        {
            return new UserModel
            {
                Key = entity.Key.Path.Last().Id,
                GoogleId = entity["googleID"]?.StringValue,
                ApiKey = entity["apiKey"]?.StringValue,
                CanonicalName = entity["canonicalName"]?.StringValue,
                IsOrganisation = entity["isOrganisation"]?.BooleanValue ?? false,
                UniqueName = entity["uniqueName"]?.StringValue
            };
        }

        private PackageModel MapPackage(Entity entity)
        {
            return new PackageModel
            {
                Key = entity.Key.Path.Last().Id,
                GoogleId = entity["googleID"]?.StringValue,
                Name = entity["name"]?.StringValue,
                Type = entity["type"]?.StringValue,
                GitUrl = entity["gitURL"]?.StringValue,
                Description = entity["description"]?.StringValue,
                DefaultBranch = entity["defaultBranch"]?.StringValue
            };
        }

        private BranchModel MapBranch(Entity entity)
        {
            return new BranchModel
            {
                Key = entity.Key.Path.Last().Id,
                GoogleId = entity["googleID"]?.StringValue,
                PackageName = entity["packageName"]?.StringValue,
                BranchName = entity["branchName"]?.StringValue,
                VersionName = entity["versionName"]?.StringValue,
                IsAutoBranch = false
            };
        }

        private VersionModel MapVersion(Entity entity)
        {
            return new VersionModel
            {
                Key = entity.Key.Path.Last().Id,
                GoogleId = entity["googleID"]?.StringValue,
                ArchiveType = entity["archiveType"]?.StringValue,
                PackageName = entity["packageName"]?.StringValue,
                PlatformName = entity["platformName"]?.StringValue,
                VersionName = entity["versionName"]?.StringValue,
                HasFile = entity["hasFile"]?.BooleanValue ?? false
            };
        }
    }
}
